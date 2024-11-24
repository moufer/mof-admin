import { createApp } from "vue";
import { createPinia } from "pinia";

import ElementPlus from "element-plus";
import zhCn from "lib/element-plus@2.8.5/locale/zh-cn.mjs";
import * as ElementPlusIconsVue from "element-plus-icons-vue";
import http from "http";

import { mofRouter } from "comm/router.js";
import { getConfig } from "comm/config.js";
import { useConfigStore } from "app/system/store/configStore.js";

const App = {
  template: /*html*/ `
    <router-view></router-view>
  `,
};

const app = createApp(App);

getConfig()
  .then((data) => {
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
