import { defineStore } from "pinia";
import api from "/src/modules/system/common/api.js";

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
      data.avatar = data.avatar || "/assets/images/avatar.jpg"; //头像
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
      //检测url里是否有key参数
      const urlParams = new URLSearchParams(window.location.search);
      const key = urlParams.get("key");
      let data = {};
      if (key) {
        data = (await api.passport.login(key)).data;
      } else {
        const token = localStorage.getItem("admin_token");
        if (!token) return false;
        data = (await api.passport.info(window.__LOGIN_MODULE__)).data;
      }
      this.setUser(data.user).setToken(data.token.token);
      return true;
    },

    async logout() {
      await api.passport.logout();
      this.clear();
    },

    clear() {
      this.setUser({}).setToken("");
      localStorage.removeItem("admin_token");
    },
  },
});
