import { createApp } from "vue";
import { createPinia } from "pinia";

import ElementPlus from "element-plus";
import zhCn from "element-plus/locale/zh-cn";
import * as ElementPlusIconsVue from "@element-plus/icons-vue";

import http from "/src/utils/http.js";
import api from "/src/modules/system/common/api.js";
import { mofRouter } from "/src/router/index.js";
import { useConfigStore } from "/src/modules/system/store/configStore.js";
import { vendorUrl, assetUrl } from "/src/utils/index.js";

// 获取importmap
const url1 = vendorUrl("@wangeditor/editor", "editor/dist/css/style.css");
console.log("vendorUrl", url1);
const url2 = assetUrl("/css/app.css");
console.log("assetUrl", url2);

const App = {
  template: /*html*/ `
    <router-view></router-view>
  `,
};

const app = createApp(App);

api.client
  .config()
  .then(({ data }) => {
    mofRouter(app, window.__ENTER_MODULE__ || "system");

    app.use(createPinia());
    app.use(ElementPlus, { locale: zhCn });

    for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
      app.component(key, component);
    }

    useConfigStore().setConfig(data);

    app.provide("siteName", window.__SITE_NAME__);
    app.provide("http", http);
    app.provide("defaultPath", "/");

    app.mount("#app");
  })
  .catch((err) => {
    console.log(err);
  });
