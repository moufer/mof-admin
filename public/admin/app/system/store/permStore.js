import { defineStore } from "pinia";
import http from "http";

/**
 * 扁平化perms
 * @param {array} perms
 * @param {int} pid
 * @returns
 */
function flattenPerms(perms, pid = 0) {
  const result = [];
  for (const perm of perms) {
    const { children, ...rest } = perm;
    result.push({ ...rest, pid });
    if (children && Array.isArray(children)) {
      result.push(...flattenPerms(children, rest.id));
    }
  }
  return result;
}

const findPermPathsById = (id, perms) => {
  const path = [];
  const perm = perms.find((p) => p.id === id);

  if (perm) {
    path.unshift(perm.id);
    if (perm.pid !== 0) {
      const parentPath = findPermPathsById(perm.pid, perms);
      path.unshift(...parentPath);
    }
  }
  return path;
};

export const usePermStore = defineStore("perm", {
  state: () => ({
    perms: [],
    permHandler: null,
  }),

  getters: {
    flattenPerms: (stat) => flattenPerms(stat.perms),
    actions: (stat) =>
      flattenPerms(stat.perms).filter((item) => item.type === "action"),
    urls: (stat) =>
      flattenPerms(stat.perms)
        .filter((item) => item.type === "menu")
        .map((perm) => ({
          id: perm.id,
          url: "/" + perm.url,
          title: perm.title,
        })),
  },

  actions: {
    async loadPerms(force = false) {
      if (this.perms?.length && !force) {
        return this.perms;
      }
      //有自定义的获取权限菜单的方法
      if (this.permHandler) {
        return await this.permHandler.apply(this);
      } else {
        data = (await http.get(`/system/passport/perms`)).data;
        this.setPerms(data); //加路由规则
        return data;
      }
    },

    breadcrumbs(id) {
      if (id > 0) {
        const list = this.flattenPerms;
        return findPermPathsById(id, list).map(
          (id) => list.find((p) => p.id === id).title
        );
      }
      return [];
    },

    setPerms(perms) {
      this.perms = perms;
    },

    setHandler(handler) {
      this.permHandler = handler;
    },
  },
});
