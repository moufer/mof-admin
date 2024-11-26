import fs from "fs-extra";
import path from "path";
import { fileURLToPath } from "url";
import crypto from "crypto";
import { glob } from "glob";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, "..");

class SourceBuilder {
  constructor() {
    this.srcDir = path.resolve(projectRoot, "src");
    this.outDir = path.resolve(projectRoot, "public/js/dist");
    this.importMap = {
      imports: {},
    };
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

    // 生成文件 hash
    const hash = this.generateHash(content);

    // 构建扁平化的输出文件名
    const parsedPath = path.parse(file);
    // 将目录分隔符替换为短横线，创建扁平化的文件名
    const flattenedName = file
      .replace(/\\/g, "/")
      .replace(/\//g, "-")
      .replace(/\.js$/, "")
      .replace(/\.vue$/, "");
    const outputName = `${flattenedName}.${hash}${parsedPath.ext}`;
    const outputPath = path.join(this.outDir, outputName);

    // 写入文件
    await fs.writeFile(outputPath, content);

    // 更新 importmap
    const modulePath = file.replace(/\.(js|vue)$/, "").replace(/\\/g, "/");
    const importPath = `/js/dist/${outputName}`;
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
}

// 执行构建
const builder = new SourceBuilder();
builder.build().catch(console.error);
