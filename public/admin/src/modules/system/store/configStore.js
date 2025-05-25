import { defineStore } from "pinia";

export const useConfigStore = defineStore("config", {
  state: () => ({
    config: null,
  }),

  actions: {
    get(key = "", defaultValue = null) {
      if (!key.length) return this.config;
      return this.config[key] || defaultValue;
    },

    set(key, value) {
      this.config[key] = value;
    },

    setConfig(data) {
      //遍历data，赋值给config
      this.config = {};
      Object.keys(data).forEach((key) => {
        this.config[key] = data[key];
      });
    },
  },
});
