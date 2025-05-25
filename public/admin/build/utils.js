import fs from "fs-extra";
import path from "path";
import {fileURLToPath} from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export async function readPackageJson() {
    const packageJson = await fs.readJson(
        path.resolve(__dirname, "../package.json")
    );
    const versions = {};

    // 合并 dependencies 和 devDependencies
    const allDependencies = {
        ...packageJson.dependencies,
        //...packageJson.devDependencies,
    };

    // 移除版本号中的 ^ 或 ~
    for (const [name, version] of Object.entries(allDependencies)) {
        versions[name] = version.replace(/[\^~]/, "");
    }

    return versions;
}

export async function generateImportMap(vendorConfig, versions) {
    const imports = {};

    for (const category of Object.values(vendorConfig)) {
        for (const pkg of category) {
            const version = versions[pkg.packageName];
            const outputPath = pkg.output(version);
            // 转换为网页可访问的路径
            const webPath = outputPath.replace("./public", "");
            imports[pkg.name] = webPath;
        }
    }

    return {imports};
}

export function getPackageVersion(packageName) {
    try {
        const packageJson = require(`../node_modules/${packageName}/package.json`);
        return packageJson.version;
    } catch (error) {
        console.error(`Error reading version for ${packageName}:`, error);
        return null;
    }
}
