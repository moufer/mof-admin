import { defineStore } from "pinia";

export const useMiniappStore = defineStore("miniapp", {
  state: () => ({
    miniapp: null,
  }),

  getters: {
    perms: (state) => state.miniapp?.perms,
  },

  actions: {
    setMiniapp(data) {
      data.avatar_img = data.avatar_img || "./assets/images/avatar.jpg";
      this.miniapp = data;
    },
  },
});
