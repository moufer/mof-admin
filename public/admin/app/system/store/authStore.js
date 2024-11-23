import { defineStore } from "pinia";
import http from "http";
import { usePermStore } from "./permStore.js";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: {},
    token: "",
  }),

  getters: {
    isLogin: (state) => state.user?.id !== undefined,
  },

  actions: {
    setUser(data) {
      data.avatar = data.avatar || "./resources/images/avatar.jpg"; //头像
      //遍历data，赋值给user
      Object.keys(data).forEach((key) => {
        this.user[key] = data[key];
      });
      return this;
    },

    setToken(token) {
      this.token = token;
      localStorage.setItem("admin_token", token);
      return this;
    },

    async autoLogin() {
      const token = localStorage.getItem("admin_token");
      if (!token) return false;
      const res = await http.get(
        `/system/passport/token?source=${window.__LOGIN_MODULE__}`
      );
      this.setUser(res.data.user).setToken(token);
      if (res.data.perms?.length) {
        usePermStore().setPerms(res.data.perms);
      }
      return true;
    },

    async logout() {
      await http.post("/system/passport/logout");
      this.clear();
    },

    clear() {
      this.setUser({}).setToken("");
      localStorage.removeItem("admin_token");
    },
  },
});
