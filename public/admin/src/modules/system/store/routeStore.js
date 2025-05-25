import { defineStore } from "pinia";
import { useRouter } from "vue-router";
import api from "/src/modules/system/common/api.js";

const router = useRouter;

export const useRouteStore = defineStore("route", {
  state() {
    return {
      rawPerms: {},
      permsList: {},
      currentRoute: {},
      urls: [],
      actions: [],
    };
  },

  getters: {
    currentRoutePath: (state) => {
      return state.currentRoute?.path;
    },

    //面包屑
    breadcrumbs: (state) => {
      let id = state.currentRoute?.meta?.id;
      if (id > 0) {
        const path = this.findPermPathsById(id, state.permsList);
        return path.map((id) => state.permsList.find((p) => p.id === id).title);
      }
      return [];
    },
  },

  actions: {
    async loadPerms() {
      const { data } = await api.passport.info("system");
      this.setPerms(data); //加路由规则
    },

    setCurrentRoute(to) {
      this.currentRoute = to;
    },

    setPerms(data) {
      //设置路由
      this.addRoutes(data);
      //保存权限
      this.rawPerms = data;
      //把perms（有children的树形结构），转换成无children的扁平列表结构
      this.permsList = this.flattenPerms(data);
      this.urls = this.permsList
        .filter((item) => item.type === "menu")
        .map((perm) => {
          return { id: perm.id, url: perm.url, title: perm.title };
        });
      this.urls.unshift({ id: 0, url: "/index", title: "首页" });
      this.actions = this.permsList.filter((item) => item.type === "action");
    },

    //添加路由
    addRoutes(perms, params = {}) {
      perms.forEach((perm) => {
        if (perm.type === "menu") {
          router.addRoute("index", {
            path: perm.url,
            meta: { title: perm.title, id: perm.id, pid: perm.pid },
            component: () => import(`@/modules/${perm.url}.js`),
          });
        } else if (perm.type === "group" && perm.children.length > 0) {
          this.addRoutes(perm.children, params);
        }
      });
    },

    //扁平化perms
    flattenPerms(perms, pid = 0) {
      const result = [];
      for (const perm of perms) {
        const { children, ...rest } = perm;
        result.push({ ...rest, pid });
        if (children && Array.isArray(children)) {
          result.push(...this.flattenPerms(children, rest.id));
        }
      }
      return result;
    },

    //找perms的path
    findPermPathsById(id, perms) {
      const path = [];
      const perm = perms.find((p) => p.id === id);

      if (perm) {
        path.unshift(perm.id);
        if (perm.pid !== 0) {
          const parentPath = this.findPermPathsById(perm.pid, perms);
          path.unshift(...parentPath);
        }
      }
      return path;
    },
  },
});
