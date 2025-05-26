import { createApp } from "vue";
import { createPinia } from "pinia";
import { useRouter } from "vue-router";
import ElementPlus from "element-plus";
import zhCn from "element-plus/locale/zh-cn";
import * as ElementPlusIconsVue from "@element-plus/icons-vue";

import http from "/src/utils/http.js";
import api from "/src/modules/system/common/api.js";
import miniappApi from "/src/modules/miniapp/common/api.js";

import { mofRouter } from "/src/router/index.js";

import { useConfigStore } from "/src/modules/system/store/configStore.js";
import { usePermStore } from "/src/modules/system/store/permStore.js";
import { useMiniappStore } from "/src/modules/miniapp/store/miniappStore.js";

const App = {
  setup() {
    const router = useRouter();
    const permStore = usePermStore();

    //获取小程序ID参数
    const id = new URLSearchParams(window.location.search).get("id");
    //定义权限菜单获取方法
    permStore.setHandler(async function () {
      if (!id) return [];
      const { data } = await miniappApi.miniapp.read(id);
      useMiniappStore().setMiniapp(data);
      this.setPerms(data.perms); //此处this指向permStore
      return data.perms;
    });

    if (!id || !/[0-9]+/.test(id)) {
      router.push("/enter");
    }
  },

  template: /*html*/ `
    <router-view></router-view>
  `,
};

const app = createApp(App);

api.client
  .config()
  .then(({ data }) => {
    //增加路由
    const extraRouters = [
      {
        name: "enter",
        path: "/enter",
        meta: { title: "磨锋小程序管理平台", requiresAuth: true },
        component: () => import(`/src/modules/miniapp/enter.js`),
      },
    ];
    mofRouter(app, "miniapp", extraRouters);

    app.use(createPinia());
    app.use(ElementPlus, { locale: zhCn });

    for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
      app.component(key, component);
    }

    //系统参数
    useConfigStore().setConfig(data);

    app.provide("siteName", window.__SITE_NAME__);
    app.provide("http", http);
    app.provide("defaultPath", "/enter"); //默认入口

    app.mount("#app");
  })
  .catch((err) => {
    console.log(err);
  });
