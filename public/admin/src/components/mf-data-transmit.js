import { buildQueryString } from "/src/utils/index.js";

export default {
  name: "mf-data-transmit",
  inject: ["http"],
  props: {
    baseUrl: {
      type: String,
      required: true,
    },
    actions: {
      type: Object,
      required: true,
    },
    dataPk: {
      type: String,
      default: "id",
    },
  },
  methods: {
    //加载数据
    search(query = {}, order = {}, page = {}) {
      let url = this.baseUrl + this.actions.search;
      //url = '/resources/data/plugin.json';
      //把this.search.query转换成url参数
      let params = new URLSearchParams();
      //把query转换成url参数
      for (let key in query) {
        let keyName = key;
        params.append(`params[${key}]`, query[key]);
      }
      //排序字段
      if (order.field) {
        params.append("order[field]", order.field);
        params.append("order[sort]", order.sort || "desc");
      }
      //把分页参数转换成url参数
      params.append("page", page.pageNum || 1);
      params.append("page_size", page.pageSize || 10);
      //把排序参数转换成url参数
      if (params.toString()) {
        url += "?" + params.toString();
      }
      return new Promise((resolve, reject) => {
        //从服务器加载数据
        this.http
          .get(url)
          .then((res) => {
            resolve(res);
          })
          .catch((err) => {
            console.log(err);
            reject(err);
          });
      });
    },
    add(params = null) {
      let url = this.baseUrl + this.actions.add;
      //如果params有值，则作为url的参数
      if (params) {
        url += (url.indexOf("?") > -1 ? "&" : "?") + buildQueryString(params);
      }
      return this.http.get(url);
    },
    edit(id, params = null) {
      let url = this.baseUrl + this.actions.edit.replace("{id}", id);
      if (params) {
        url += (url.indexOf("?") > -1 ? "&" : "?") + buildQueryString(params);
      }
      return this.http.get(url);
    },
    //读取一条记录
    read(id, params = null) {
      let url = this.baseUrl + this.actions.read.replace("{id}", id);
      if (params) {
        url += (url.indexOf("?") > -1 ? "&" : "?") + buildQueryString(params);
      }
      return this.http.get(url);
    },
    //更新数据
    update(id, data) {
      let url = this.baseUrl + this.actions.update.replace("{id}", id);
      let params = {};
      //params[this.dataPk] = id;
      return this.http.put(url, data, params);
    },
    //提交新增
    create(data) {
      let url = this.baseUrl + this.actions.create;
      return this.http.post(url, data);
    },
    //提交删除
    delete: function (ids) {
      if (ids.length > 1) {
        return this.deleteMany(ids);
      }
      let url =
        this.baseUrl + this.actions.delete.replace("{id}", ids.join(","));
      return this.http.delete(url);
    },
    //批量删除
    deleteMany: function (ids) {
      let url = this.baseUrl + this.actions.deletes;
      let params = {};
      params[this.dataPk] = ids;
      return this.http.post(url, params);
    },
    //批量更新
    batch: function (ids, field, value) {
      //提交到服务器更新
      let url = this.baseUrl + this.actions.updates;
      let params = { field, value };
      params[this.dataPk] = ids;
      return this.http.put(url, params);
    },
    //保存
    save: function (data) {
      if (data[this.dataPk]) {
        return this.update(data[this.dataPk], data);
      } else {
        return this.create(data);
      }
    },
  },
  template: `<div></div>`,
};
