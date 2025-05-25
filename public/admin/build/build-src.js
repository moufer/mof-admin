import fs from "fs-extra";
import path from "path";
import { fileURLToPath } from "url";
import crypto from "crypto";
import { glob } from "glob";
import dotenv from "dotenv";
import * as esbuild from "esbuild";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, "..");
console.log("projectRoot", projectRoot);

// 加载环境变量
const envFile =
  process.env.NODE_ENV === "production"
    ? ".env.production"
    : ".env.development";
dotenv.config({ path: path.resolve(projectRoot, envFile) });

class SourceBuilder {
  constructor() {
    this.srcDir = path.resolve(projectRoot, "src");
    // 使用 DIST_DIR 构建输出路径
    this.outDir = path.resolve(
      projectRoot,
      path.join(process.env.DIST_DIR, "src")
    );
    this.importMap = {
      imports: {},
    };
    // 添加资源文件缓存
    this.assetHashCache = new Map();
  }

  async build() {
    try {
      console.log("\nStarting build process...");

      // 清理输出目录
      await fs.emptyDir(this.outDir);

      // 获取所有源文件
      const files = await this.getSourceFiles();
      console.log(`Found ${files.length} files to process`);

      // 处理所有文件
      for (const file of files) {
        await this.processFile(file);
      }

      // 生成 importmap
      await this.generateImportMap();

      console.log("Build completed successfully!");
    } catch (error) {
      console.error("Build failed:", error);
      process.exit(1);
    }
  }

  async getSourceFiles() {
    return glob("**/*.{js,vue}", {
      cwd: this.srcDir,
      ignore: ["**/node_modules/**"],
    });
  }

  async processFile(file) {
    const inputPath = path.join(this.srcDir, file);
    let content = await fs.readFile(inputPath, "utf-8");

    // 替换导入语句
    content = this.replaceImports(content);

    // 添加处理 assetUrl 方法
    if (process.env.NODE_ENV === "production") {
      content = this.processAssetUrls(content);
    }

    // 生成文件 hash
    const hash = this.generateHash(content);

    // 构建扁平化的输出文件名
    const parsedPath = path.parse(file);
    const flattenedName = file
      .replace(/\\/g, "/")
      .replace(/\//g, "-")
      .replace(/\.js$/, "")
      .replace(/\.vue$/, "");
    const outputName = `${flattenedName}.${hash}${parsedPath.ext}`;
    const outputPath = path.join(this.outDir, outputName);

    // 添加：在生产环境下压缩代码
    if (process.env.NODE_ENV === "production") {
      try {
        const result = await esbuild.transform(content, {
          minify: true,
          target: ["es2020"],
          format: "esm",
          loader: parsedPath.ext.slice(1), // 移除点号，如 '.js' -> 'js'
          sourcemap: true,
        });

        // 写入压缩后的代码
        await fs.writeFile(outputPath, result.code);
        // 写入 sourcemap
        await fs.writeFile(`${outputPath}.map`, result.map);

        // 在代码末尾添加 sourcemap 引用
        await fs.appendFile(
          outputPath,
          `\n//# sourceMappingURL=${path.basename(outputPath)}.map`
        );
      } catch (error) {
        console.error(`Error minifying ${file}:`, error);
        // 如果压缩失败，使用原始内容
        await fs.writeFile(outputPath, content);
      }
    } else {
      // 开发环境下使用原始内容
      await fs.writeFile(outputPath, content);
    }

    // 更新 importmap
    const modulePath = file.replace(/\.(js|vue)$/, "").replace(/\\/g, "/");
    const importPath =
      process.env.NODE_ENV === "production"
        ? `./src/${outputName}`
        : `./${process.env.DIST_DIR}/src/${outputName}`;
    this.importMap.imports[`@/${modulePath}`] = importPath;

    console.log(`Processed: ${file} -> ${outputName}`);
  }

  replaceImports(content) {
    // 替换静态导入
    content = content.replace(
      /import\s+(?:(?:\{[^}]*\}|\*\s+as\s+[^,\s]+|\w+)\s*,?\s*)*\s*from\s+['"]\/src\/([^'"]+)\.js['"]/g,
      (match, path) => {
        return match.replace(`/src/${path}.js`, `@/${path}`);
      }
    );

    // 替换动态导入
    content = content.replace(
      /import\s*\(\s*(`|['"])\/src\/([^'"`]+)\.js\1\s*\)/g,
      (match, quote, path) => {
        return match.replace(`/src/${path}.js`, `@/${path}`);
      }
    );

    // 替换带变量的动态导入
    content = content.replace(
      /import\s*\(\s*`\/src\/([^`]+)\.js`\s*\)/g,
      (match, path) => {
        return match.replace(`/src/${path}.js`, `@/${path}`);
      }
    );

    return content;
  }

  generateHash(content) {
    return crypto
      .createHash("md5")
      .update(content)
      .digest("hex")
      .substring(0, 8);
  }

  async generateImportMap() {
    const importMapPath = path.join(this.outDir, "import-map.json");
    await fs.writeJson(importMapPath, this.importMap, { spaces: 2 });
    console.log(
      `Generated import map with ${
        Object.keys(this.importMap.imports).length
      } entries`
    );
  }

  // 修改 processAssetUrls 方法，移除异步操作
  processAssetUrls(content) {
    return content.replace(
      /assetUrl\(['"](\/[^'"]+)['"]\)/g,
      (match, assetPath) => {
        try {
          // 构建完整的资产文件路径
          const fullAssetPath = path.join(
            projectRoot,
            process.env.DIST_DIR,
            "assets",
            assetPath.slice(1)
          );

          // 检查文件是否存在
          if (!fs.pathExistsSync(fullAssetPath)) {
            console.warn(`Warning: Asset file not found: ${fullAssetPath}`);
            return match;
          }

          // 从缓存中获取 hash，如果没有则生成新的
          let hash = this.assetHashCache.get(fullAssetPath);
          if (!hash) {
            const fileContent = fs.readFileSync(fullAssetPath);
            hash = this.generateHash(fileContent);
            this.assetHashCache.set(fullAssetPath, hash);
          }

          // 返回带 hash 的资源 URL
          return `assetUrl('${assetPath}?${hash}')`;
        } catch (error) {
          console.error(`Error processing asset URL: ${assetPath}`, error);
          return match;
        }
      }
    );
  }
}

// 执行构建
const builder = new SourceBuilder();
builder.build().catch(console.error);
