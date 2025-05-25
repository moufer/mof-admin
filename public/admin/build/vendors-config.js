import { readPackageJson } from "./utils.js";
import dotenv from "dotenv";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, "..");

// 加载环境变量
const envFile =
  process.env.NODE_ENV === "production"
    ? ".env.production"
    : ".env.development";
dotenv.config({ path: path.resolve(projectRoot, envFile) });

const versions = await readPackageJson();

// 修改：使用 DIST_DIR 替代 BUILD_VENDORS_DIR
const distPath = path.join(process.env.DIST_DIR, "vendor");

export const vendorConfig = {
  // 基础库
  base: [
    {
      name: "vue",
      input: "./node_modules/vue/dist/vue.esm-browser.js",
      output: (version) => `./${distPath}/vue@${version}/vue.js`,
      packageName: "vue",
      extras: [
        {
          name: "runtime",
          input: "./node_modules/vue/dist/vue.runtime.esm-browser.js",
          output: (version) => `./${distPath}/vue@${version}/vue.runtime.js`,
        },
      ],
    },
    {
      name: "pinia",
      input: "./node_modules/pinia/dist/pinia.mjs",
      output: (version) => `./${distPath}/pinia@${version}/pinia.js`,
      packageName: "pinia",
    },
    {
      name: "vue-router",
      input: "./node_modules/vue-router/dist/vue-router.mjs",
      output: (version) => `./${distPath}/vue-router@${version}/vue-router.js`,
      packageName: "vue-router",
    },
    {
      name: "lodash-es",
      input: "./node_modules/lodash-es/lodash.js",
      output: (version) => `./${distPath}/lodash@${version}/lodash.js`,
      packageName: "lodash-es",
    },
    {
      name: "axios",
      input: "./node_modules/axios/index.js",
      output: (version) => `./${distPath}/axios@${version}/axios.js`,
      packageName: "axios",
    },
    {
      name: "moment",
      input: "./node_modules/moment/dist/moment.js",
      output: (version) => `./${distPath}/moment@${version}/moment.js`,
      packageName: "moment",
      extras: [
        {
          name: "locale/zh-cn",
          input: "./node_modules/moment/locale/zh-cn.js",
          output: (version) =>
            `./${distPath}/moment@${version}/locale/zh-cn.js`,
        },
      ],
    },
    {
      name: "@vueuse/core",
      input: "./node_modules/@vueuse/core/index.mjs",
      output: (version) => `./${distPath}/vueuse@${version}/vueuse.js`,
      packageName: "@vueuse/core",
    },
    {
      name: "@vueuse/shared",
      input: "./node_modules/@vueuse/shared/index.mjs",
      output: (version) => `./${distPath}/vueuse@${version}/vueuse-shared.js`,
      packageName: "@vueuse/core",
    },
  ],

  // UI 组件库
  ui: [
    {
      name: "element-plus",
      input: "./node_modules/element-plus/es/index.mjs",
      output: (version) =>
        `./${distPath}/element-plus@${version}/element-plus.js`,
      packageName: "element-plus",
      css: [
        "./node_modules/element-plus/dist/index.css",
        // 如果有其他 CSS 文件，可以在这里添加
      ],
      extras: [
        {
          name: "locale/zh-cn",
          input: "./node_modules/element-plus/dist/locale/zh-cn.mjs",
          output: (version) =>
            `./${distPath}/element-plus@${version}/locale/zh-cn.js`,
        },
      ],
    },
    {
      name: "@element-plus/icons-vue",
      input: "./node_modules/@element-plus/icons-vue/dist/index.js",
      output: (version) =>
        `./${distPath}/element-plus-icons@${version}/icons.js`,
      packageName: "@element-plus/icons-vue",
    },
  ],

  // 图标库
  icons: [
    {
      name: "bootstrap-icons",
      packageName: "bootstrap-icons",
      // 移除 input 和 isCSS 配置
      // 直接使用 assets 来复制所有需要的文件
      assets: [
        {
          from: "./node_modules/bootstrap-icons/font/bootstrap-icons.css",
          to: (version) =>
            `./${distPath}/bootstrap-icons@${version}/bootstrap-icons.css`,
        },
        {
          from: "./node_modules/bootstrap-icons/font/fonts",
          to: (version) => `./${distPath}/bootstrap-icons@${version}/fonts`,
        },
      ],
    },
    {
      name: "@wovosoft/wovoui-icons",
      input: "./node_modules/@wovosoft/wovoui-icons/dist/index.es.mjs",
      output: (version) => `./${distPath}/wovoui-icons@${version}/icons.js`,
      packageName: "@wovosoft/wovoui-icons",
      css: ["./node_modules/@wovosoft/wovoui-icons/dist/style.css"],
    },
  ],

  // 编辑器
  editors: [
    {
      name: "@wangeditor/editor",
      input: "./node_modules/@wangeditor/editor/dist/index.esm.js",
      output: (version) => `./${distPath}/wangeditor@${version}/editor.js`,
      packageName: "@wangeditor/editor",
      css: "./node_modules/@wangeditor/editor/dist/css/style.css",
    },
    {
      name: "vue-ueditor-wrap",
      input: "./node_modules/vue-ueditor-wrap/es/vue-ueditor-wrap/index.js",
      output: (version) =>
        `./${distPath}/vue-ueditor-wrap@${version}/vue-ueditor-wrap.js`,
      packageName: "vue-ueditor-wrap",
    },
  ],

  // 工具库
  utils: [
    {
      name: "echarts",
      input: "./node_modules/echarts/dist/echarts.esm.js",
      output: (version) => `./${distPath}/echarts@${version}/echarts.js`,
      packageName: "echarts",
      extras: [
        {
          name: "theme/dark",
          input: "./node_modules/echarts/theme/dark.js",
          output: (version) => `./${distPath}/echarts@${version}/theme/dark.js`,
        },
      ],
    },
    {
      name: "marked",
      input: "./node_modules/marked/lib/marked.esm.js",
      output: (version) => `./${distPath}/marked@${version}/marked.js`,
      packageName: "marked",
    },
    {
      name: "vconsole",
      input: "./node_modules/vconsole/dist/vconsole.min.js",
      output: (version) => `./${distPath}/vconsole@${version}/vconsole.js`,
      packageName: "vconsole",
    },
  ],

  ignoreClearDirs: ["ueditor-plus"],
};

// 导出一些常用的配置组合
export const presets = {
  // 最小化配置
  minimal: ["vue", "vue-router", "pinia"],

  // 基础配置
  basic: ["vue", "vue-router", "pinia", "axios", "lodash-es"],

  // 完整配置
  full: Object.values(vendorConfig)
    .flat()
    .map((pkg) => pkg.name),
};

// 导出一些工具函数
export const utils = {
  // 获取某个包的所有依赖项
  getDependencies(packageName) {
    const allPackages = Object.values(vendorConfig).flat();
    const pkg = allPackages.find((p) => p.name === packageName);
    if (!pkg) return [];

    const deps = [];
    if (pkg.extras) {
      deps.push(...pkg.extras.map((extra) => `${pkg.name}/${extra.name}`));
    }
    return deps;
  },

  // 获取某个类别的所有包
  getPackagesByCategory(category) {
    return vendorConfig[category] || [];
  },
};
