import * as esbuild from "esbuild";
import fs from "fs-extra";
import path from "path";
import { fileURLToPath } from "url";

import { vendorConfig } from "./vendors-config.js";
import { readPackageJson } from "./utils.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, "..");

class VendorBuilder {
  constructor(config) {
    this.config = config;
    this.buildTime = new Date().toISOString();
  }

  async build() {
    try {
      const versions = await readPackageJson();

      // 清理输出目录
      await this.cleanOutputDir();

      // 按类别构建
      for (const [category, packages] of Object.entries(this.config)) {
        console.log(`\nBuilding ${category} vendors...`);
        await this.buildCategory(packages, versions);
      }

      // 生成 importmap
      await this.generateImportMapFile(versions);

      console.log("\nBuild completed successfully!");
    } catch (error) {
      console.error("\nBuild failed:", error);
      process.exit(1);
    }
  }

  async cleanOutputDir() {
    const outputDir = path.resolve(projectRoot, "public/js/vendor");
    await fs.emptyDir(outputDir);
    console.log("Cleaned output directory");
  }

  async buildCategory(packages, versions) {
    for (const pkg of packages) {
      await this.buildPackage(pkg, versions[pkg.packageName]);
    }
  }

  async buildPackage(pkg, version) {
    if (!version) {
      console.error(`No version found for ${pkg.packageName}`);
      return;
    }

    console.log(`\nBuilding ${pkg.name}@${version}...`);

    try {
      // 处理纯资源包
      if (pkg.assets) {
        await this.handleAssets(pkg.assets, version);
      }

      // 如果有 input，则构建 JS
      if (pkg.input) {
        const outputPath = pkg.output(version);
        await fs.ensureDir(path.dirname(outputPath));

        await this.buildJs({
          ...pkg,
          outputPath,
          version,
        });

        // 处理 CSS
        if (pkg.css) {
          await this.handleCss(pkg, version, outputPath);
        }

        // 处理额外文件
        if (pkg.extras) {
          for (const extra of pkg.extras) {
            console.log(`Building extra: ${extra.name}`);
            const extraOutputPath = extra.output(version);
            await fs.ensureDir(path.dirname(extraOutputPath));

            await this.buildJs({
              ...pkg,
              ...extra,
              outputPath: extraOutputPath,
              version,
              packageName: pkg.packageName,
            });

            if (extra.css) {
              await this.handleCss(
                { ...pkg, css: extra.css },
                version,
                extraOutputPath
              );
            }
          }
        }
      }

      console.log(`${pkg.name}@${version} built successfully`);
    } catch (error) {
      console.error(`Error building ${pkg.name}@${version}:`, error);
      throw error;
    }
  }

  async handleAssets(assets, version) {
    for (const asset of assets) {
      const targetPath = asset.to(version);

      try {
        await fs.ensureDir(path.dirname(targetPath));

        if ((await fs.stat(asset.from)).isDirectory()) {
          await fs.copy(asset.from, targetPath);
          console.log(`Copied directory: ${asset.from} -> ${targetPath}`);
        } else {
          await fs.copy(asset.from, targetPath);

          if (targetPath.endsWith(".css")) {
            await this.fixCssUrls(targetPath);
          }

          console.log(`Copied file: ${asset.from} -> ${targetPath}`);
        }
      } catch (error) {
        console.error(`Error copying asset ${asset.from}:`, error);
        throw error;
      }
    }
  }

  async handleCss(pkg, version, outputPath) {
    try {
      const basePath = outputPath || pkg.output(version);
      const cssOutput = basePath.replace(".js", ".css");

      await fs.ensureDir(path.dirname(cssOutput));

      if (Array.isArray(pkg.css)) {
        const cssContents = await Promise.all(
          pkg.css.map((cssPath) => fs.readFile(cssPath, "utf-8"))
        );
        await fs.writeFile(cssOutput, cssContents.join("\n"));
        console.log(`Combined CSS written to ${cssOutput}`);
      } else if (typeof pkg.css === "string") {
        await fs.copy(pkg.css, cssOutput);
        console.log(`CSS copied to ${cssOutput}`);
      }
    } catch (error) {
      console.error(`Error handling CSS for ${pkg.name}:`, error);
      throw error;
    }
  }

  async fixCssUrls(cssPath) {
    try {
      let content = await fs.readFile(cssPath, "utf8");

      // 修复字体文件的相对路径
      content = content.replace(/url\("\.\/fonts\//g, 'url("./fonts/');

      await fs.writeFile(cssPath, content);
    } catch (error) {
      console.error(`Error fixing CSS URLs in ${cssPath}:`, error);
      throw error;
    }
  }

  async buildJs({ input, outputPath, version, name, packageName }) {
    const buildOptions = {
      entryPoints: [input],
      bundle: true,
      format: "esm",
      outfile: outputPath,
      sourcemap: true,
      minify: process.env.NODE_ENV === "production",
      target: ["es2020"],
      platform: "browser",
      define: {
        "process.env.NODE_ENV": `"${process.env.NODE_ENV || "development"}"`,
        global: "window",
      },
      metafile: true,
      external: [
        "vue",
        "vue-router",
        "pinia",
        "lodash",
        "axios",
        "moment",
        "@element-plus/icons-vue",
      ],
    };

    try {
      const result = await esbuild.build(buildOptions);

      if (result.metafile) {
        const pkgName = packageName || name.split("/")[0];
        await this.handleBuildMetadata(result.metafile, {
          name: pkgName,
          version,
          outputPath,
        });
      }
    } catch (error) {
      console.error(`Error building JS for ${name}:`, error);
      throw error;
    }
  }

  async handleBuildMetadata(metafile, { name, version, outputPath }) {
    const outputDir = path.dirname(outputPath);
    const metaPath = path.join(outputDir, "meta.json");

    await fs.ensureDir(outputDir);

    await fs.writeJson(
      metaPath,
      {
        name,
        version,
        buildTime: this.buildTime,
        meta: metafile,
      },
      { spaces: 2 }
    );
  }

  async generateImportMapFile(versions) {
    const importMap = {
      imports: {},
      scopes: {},
    };

    for (const category of Object.values(this.config)) {
      for (const pkg of category) {
        const version = versions[pkg.packageName];

        // 对于有 input 的包，添加 JS 模块映射
        if (pkg.input) {
          const mainPath = pkg.output(version).replace("./public", "");
          importMap.imports[pkg.name] = mainPath;

          // 添加额外文件
          if (pkg.extras) {
            for (const extra of pkg.extras) {
              const extraPath = extra.output(version).replace("./public", "");
              importMap.imports[`${pkg.name}/${extra.name}`] = extraPath;
            }
          }
        }

        // 对于纯资源包，添加 CSS 路径映射
        if (pkg.assets) {
          const cssAsset = pkg.assets.find((a) =>
            a.to(version).endsWith(".css")
          );
          if (cssAsset) {
            const cssPath = cssAsset.to(version).replace("./public", "");
            importMap.imports[`${pkg.name}/style`] = cssPath;
          }
        }
      }
    }

    // 生成 importmap 文件
    const importMapPath = "./public/js/vendor/import-map.json";
    await fs.ensureDir(path.dirname(importMapPath));
    await fs.writeJson(importMapPath, importMap, { spaces: 2 });

    // 生成版本信息文件
    const versionsPath = "./public/js/vendor/versions.json";
    await fs.writeJson(
      versionsPath,
      {
        buildTime: this.buildTime,
        versions,
      },
      { spaces: 2 }
    );

    console.log("Import map and versions generated successfully");
  }
}

// 执行构建
const builder = new VendorBuilder(vendorConfig);
builder.build().catch((error) => {
  console.error("Build failed:", error);
  process.exit(1);
});
