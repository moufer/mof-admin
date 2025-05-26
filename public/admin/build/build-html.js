import fs from "fs-extra";
import path from "path";
import { fileURLToPath } from "url";
import dotenv from "dotenv";
import crypto from "crypto";
import * as esbuild from "esbuild";
import { glob } from "glob";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, "..");

// 加载环境变量
const envFile =
  process.env.NODE_ENV === "production"
    ? ".env.production"
    : ".env.development";
dotenv.config({ path: path.resolve(projectRoot, envFile) });

// 根据production来决定生成路径
const isProduction = process.env.NODE_ENV === "production";
const vendorsDistPath = path.join(process.env.DIST_DIR, "vendor");
const srcDistPath = path.join(process.env.DIST_DIR, "src");
const assetsDistPath = path.join(process.env.DIST_DIR, "assets");
const indexHtmlPath = path.join(process.env.DIST_DIR, "index.html");

// 自动查找 public/admin 目录下所有 html 文件
function findAllHtmlFiles(dir) {
  return new Promise((resolve, reject) => {
    glob(`${dir}/**/*.html`, { nodir: true }, (err, files) => {
      if (err) reject(err);
      else resolve(files);
    });
  });
}

// 复制文件到目标目录
async function copyHtmlFile(sourceHtmlPath) {
  const fileName = path.basename(sourceHtmlPath);
  const targetHtmlPath = isProduction
    ? path.resolve(projectRoot, process.env.DIST_DIR, fileName)
    : path.resolve(projectRoot, fileName);
  await fs.ensureDir(path.dirname(targetHtmlPath));
  await fs.copy(sourceHtmlPath, targetHtmlPath);
  console.log(`Copied (no build): ${sourceHtmlPath} -> ${targetHtmlPath}`);
}

async function buildHtml(htmlFileName) {
  try {
    // 读取源 HTML 路径
    const sourceHtmlPath = path.resolve(projectRoot, htmlFileName);
    let htmlContent = await fs.readFile(sourceHtmlPath, "utf-8");

    htmlContent = await handleVendorLinks(htmlContent);
    // 如果是生产环境，复制 assets 文件夹并处理 CSS 路径
    if (isProduction) {
      await handleAssets();
      htmlContent = await handleCssLinks(htmlContent);
    }

    // 读取 vendors 的 import-map.json
    const vendorsMapPath = path.resolve(
      projectRoot,
      vendorsDistPath,
      "import-map.json"
    );
    const vendorsMap = await fs.readJson(vendorsMapPath);

    // 初始化合并后的 imports，使用 srcDistPath
    let mergedImports = {
      "@/": `./${srcDistPath}/`,
    };

    if (isProduction) {
      // 如果是生产环境，简化路径
      mergedImports["@/"] = "./src/";
    }

    // 合并 vendors 的 imports
    Object.assign(mergedImports, vendorsMap.imports || {});

    // 如果是生产环境，还需要合并 src 的 import-map.json
    if (isProduction) {
      const srcMapPath = path.resolve(
        projectRoot,
        srcDistPath,
        "import-map.json"
      );
      if (await fs.exists(srcMapPath)) {
        const srcMap = await fs.readJson(srcMapPath);

        // 修改：处理 src 的 imports 路径
        for (const [key, value] of Object.entries(srcMap.imports)) {
          // 将完整路径转换为相对路径
          const relativePath = value.replace(
            `./${process.env.DIST_DIR}/`,
            "./"
          );
          mergedImports[key] = relativePath;
        }
      }

      // 修改：简化所有路径处理
      for (const [key, value] of Object.entries(mergedImports)) {
        if (value.startsWith(`./`)) {
          // 确保所有路径都使用正确的前缀
          if (value.includes(process.env.DIST_DIR)) {
            mergedImports[key] = value.replace(
              `./${process.env.DIST_DIR}/`,
              "./"
            );
          }
        }
      }
    }

    // 移除现有的 importmap
    htmlContent = htmlContent.replace(
      /<script\s+type="importmap"[\s\S]*?<\/script>/i,
      ""
    );

    // 替换 window.serverUrl
    htmlContent = htmlContent.replace(
      /window\.serverUrl\s*=\s*['"].*?['"];?/,
      `window.serverUrl = "${process.env.SERVER_URL}";`
    );

    if (isProduction) {
      // 修改：获取并处理 main 模块路径
      // 针对不同页面，自动查找 main module 路径
      let mainModuleKey = null;
      // 从文件名获取模块名
      const moduleName = htmlFileName.replace(".html", "");
      mainModuleKey =
        moduleName === "index"
          ? "@/modules/system/common/main"
          : `@/modules/${moduleName}/common/main`;
      const mainModulePath = mergedImports[mainModuleKey];
      if (!mainModulePath) {
        throw new Error(
          `Main module path not found in import map for ${htmlFileName}`
        );
      }

      // 替换 module src 路径
      htmlContent = htmlContent.replace(
        /<script type="module"[^>]+defer[^>]*><\/script>/,
        `<script type="module" src="${mainModulePath}" defer></script>`
      );

      // 删除 socket.io 相关代码
      htmlContent = htmlContent
        .replace(/<script[^>]+socket\.io\.js[^>]*><\/script>\s*/, "")
        .replace(/<script>\s*const socket[^<]*<\/script>\s*/, "");
    }

    // 构建新的 importmap
    const importMapScript = `
    <script type="importmap">
        {
            "imports": ${JSON.stringify(mergedImports, null, 16)}
        }
    </script>`;

    // 在 </head> 标签前插入新的 importmap
    htmlContent = htmlContent.replace("</head>", `${importMapScript}\n</head>`);

    // 使用 DIST_DIR 构建目标路径
    const targetHtmlPath = isProduction
      ? path.resolve(projectRoot, process.env.DIST_DIR, htmlFileName)
      : path.resolve(projectRoot, htmlFileName);

    // 确保目标目录存在
    await fs.ensureDir(path.dirname(targetHtmlPath));

    // 写入文件
    await fs.writeFile(targetHtmlPath, htmlContent);

    console.log(`Successfully updated importmap in ${targetHtmlPath}`);
  } catch (error) {
    console.error(`Error building HTML for ${htmlFileName}:`, error);
    process.exit(1);
  }
}

// 修改 handleCssLinks 函数，添加 CSS 压缩功能
async function handleCssLinks(htmlContent) {
  const cssLinkRegex =
    /<link[^>]*href=["']\.\/assets\/([^"']+\.css)["'][^>]*>/g;

  const indexHtmlDir = path.dirname(
    path.join(process.env.DIST_DIR, "index.html")
  );

  const replacements = [];

  let match;
  while ((match = cssLinkRegex.exec(htmlContent)) !== null) {
    const fullMatch = match[0];
    const cssPath = match[1];

    try {
      const sourceFile = path.resolve(projectRoot, "assets", cssPath);
      const targetFile = path.resolve(
        projectRoot,
        process.env.DIST_DIR,
        "assets",
        cssPath
      );

      if (await fs.exists(sourceFile)) {
        // 读取原始 CSS 内容
        const fileContent = await fs.readFile(sourceFile, "utf-8");

        // 在生产环境下压缩 CSS
        let processedContent = fileContent;
        if (isProduction) {
          try {
            const result = await esbuild.transform(fileContent, {
              loader: "css",
              minify: true,
              sourcemap: true,
            });
            processedContent = result.code;

            // 确保目标目录存在
            await fs.ensureDir(path.dirname(targetFile));

            // 写入压缩后的 CSS
            await fs.writeFile(targetFile, result.code);
            // 写入 sourcemap
            await fs.writeFile(`${targetFile}.map`, result.map);
            // 在 CSS 文件末尾添加 sourcemap 引用
            await fs.appendFile(
              targetFile,
              `\n/*# sourceMappingURL=${path.basename(targetFile)}.map */`
            );
          } catch (error) {
            console.warn(
              `Warning: Error minifying CSS file ${cssPath}:`,
              error
            );
            // 如果压缩失败，使用原始内容
            await fs.writeFile(targetFile, fileContent);
          }
        } else {
          // 开发环境直接复制文件
          await fs.copy(sourceFile, targetFile);
        }

        // 计算压缩后内容的 hash
        const hash = crypto
          .createHash("md5")
          .update(processedContent)
          .digest("hex")
          .substring(0, 8);

        // 构建新的 href 路径
        let newHref = `./${assetsDistPath}/${cssPath}?${hash}`;

        if (isProduction) {
          newHref = "./assets/" + `${cssPath}?${hash}`;
        }

        // 创建新的 link 标签
        const newLink = fullMatch.replace(
          /href=["']\.\/assets\/[^"']+["']/,
          `href="${newHref}"`
        );

        replacements.push({
          original: fullMatch,
          replacement: newLink,
        });

        console.log(`Processed CSS: ${cssPath}`);
      }
    } catch (error) {
      console.warn(`Warning: Error processing CSS file ${cssPath}:`, error);
    }
  }

  let updatedContent = htmlContent;
  for (const { original, replacement } of replacements) {
    updatedContent = updatedContent.replace(original, replacement);
  }

  return updatedContent;
}

// 简化后的 handleAssets 函数，增加忽略选项
async function handleAssets() {
  // 修改：直接使用 DIST_DIR + '/assets'
  const assetsDir = path.join(process.env.DIST_DIR, "assets");
  if (!process.env.DIST_DIR) {
    console.warn("DIST_DIR not defined in environment, skipping assets copy");
    return;
  }

  const sourceAssetsDir = path.resolve(projectRoot, "assets");
  const targetAssetsDir = path.resolve(projectRoot, assetsDistPath);

  try {
    // 检查源 assets 目录是否存在
    if (await fs.exists(sourceAssetsDir)) {
      // 确保目标目录存在
      await fs.ensureDir(targetAssetsDir);

      // 复制整个 assets 文件夹，添加忽略选项
      await fs.copy(sourceAssetsDir, targetAssetsDir, {
        filter: (src) => {
          // 忽略 .less 和 .scss 文件
          return !src.endsWith(".less") && !src.endsWith(".scss");
        },
      });

      console.log(
        `Copied assets directory: ${sourceAssetsDir} -> ${targetAssetsDir} (ignoring .less and .scss files)`
      );
    } else {
      console.log("Source assets directory does not exist, skipping copy");
    }
  } catch (error) {
    console.error("Error copying assets directory:", error);
    throw error;
  }
}

// 添加处理 vendor 链接的函数
async function handleVendorLinks(htmlContent) {
  // 匹配所有 link 标签中引用 vendor 目录下的文件
  const vendorLinkRegex = /<link[^>]*href=["']\.\/vendor\/([^"']+)["'][^>]*>/g;

  // 存储所有需要替换的内容
  const replacements = [];

  // 查找所有匹配项
  let match;
  while ((match = vendorLinkRegex.exec(htmlContent)) !== null) {
    const fullMatch = match[0];
    const vendorPath = match[1];

    try {
      // 检查是否包含版本号（@符号）
      if (vendorPath.includes("@")) {
        // 提取包名、版本和文件路径
        const [packageName, version, ...rest] = vendorPath.split("@")[0];
        const filePath = rest.join("@");

        // 检查 vendor 目录下是否存在该包的其他版本
        const vendorDir = path.resolve(projectRoot, vendorsDistPath);
        const packages = await fs.readdir(vendorDir);

        // 查找匹配的包
        const matchingPackage = packages.find((p) =>
          p.startsWith(`${packageName}@`)
        );

        if (matchingPackage) {
          const newVersion = matchingPackage.split("@")[1];

          // 如果版本不同，更新链接
          if (newVersion !== version) {
            const newHref = isProduction
              ? `./vendor/${packageName}@${newVersion}/${filePath}`
              : `./${vendorsDistPath}/${packageName}@${newVersion}/${filePath}`;

            const newLink = fullMatch.replace(
              /href=["'][^"']+["']/,
              `href="${newHref}"`
            );

            replacements.push({
              original: fullMatch,
              replacement: newLink,
            });

            console.log(
              `Updated vendor link: ${packageName} from ${version} to ${newVersion}`
            );
          }
        }
      } else {
        // 处理不包含版本号的普通 vendor 路径
        const newHref = isProduction
          ? `./vendor/${vendorPath}`
          : `./${vendorsDistPath}/${vendorPath}`;

        const newLink = fullMatch.replace(
          /href=["'][^"']+["']/,
          `href="${newHref}"`
        );

        replacements.push({
          original: fullMatch,
          replacement: newLink,
        });
      }
    } catch (error) {
      console.warn(
        `Warning: Error processing vendor link for ${vendorPath}:`,
        error
      );
    }
  }

  // 执行所有替换
  let updatedContent = htmlContent;
  for (const { original, replacement } of replacements) {
    updatedContent = updatedContent.replace(original, replacement);
  }

  return updatedContent;
}

// 执行构建
(async () => {
  const htmlFiles = await findAllHtmlFiles(
    path.resolve(projectRoot, "public/admin")
  );
  for (const htmlFile of htmlFiles) {
    const content = await fs.readFile(htmlFile, "utf-8");
    if (
      content.includes("window.__LOGIN_MODULE__") &&
      content.includes('<script type="importmap"') &&
      content.includes('<script type="module"')
    ) {
      await buildHtml(path.basename(htmlFile));
    } else {
      if (isProduction) {
        await copyHtmlFile(htmlFile);
      }
    }
  }
})();
