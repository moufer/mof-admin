import axios from "axios";
import { ElMessage } from "element-plus";
import { getRouter } from "/src/router/index.js";

const http = axios.create({
  baseURL: window.serverUrl,
});

http.interceptors.request.use(
  function (cfg) {
    // 判断是否存在token，如果存在将每个页面header都添加token
    const token = localStorage.getItem("admin_token");
    if (token) {
      cfg.headers.Authorization = "Bearer " + token;
    }
    //application/json; charset=utf-8
    cfg.headers["Content-Type"] = "application/json; charset=utf-8";
    //从localStorage中获取获取X-Session-Id
    // const sessionId = localStorage.getItem("X-Session-Id");
    // if (sessionId) {
    //   cfg.headers["X-Session-Id"] = sessionId;
    // }
    return cfg;
  },
  function (err) {
    return Promise.reject(err);
  }
);

http.interceptors.response.use(
  function (res) {
    if (res.data.errcode === 0) {
      return res.data; //成功
    } else {
      return Promise.reject(res.data); //错误信息
    }
  },
  function (err) {
    let errMsg = "";
    if (err.response.data && err.response.data.errmsg) {
      errMsg = err.response.data.errmsg;
    }
    // 对响应错误做点什么
    if (err.response.status === 401) {
      //localStorage.removeItem("admin_token");
      const router = getRouter();
      if ("/login" !== router.currentRoute.value.path) {
        const currentPath = router.currentRoute.value.fullPath;
        ElMessage.error(errMsg ? errMsg : "登录已过期，请重新登录");
        setTimeout(() => {
          router.push({
            path: "/login",
            query: { forward: encodeURIComponent(currentPath) },
          });
        }, 1000);
      }
    } else if (err.response.status === 403) {
      ElMessage.error(errMsg ? errMsg : "权限不足");
    } else if (err.response.status === 404) {
      ElMessage.error(errMsg ? errMsg : "请求不存在");
    } else if (err.response.status !== 200) {
      ElMessage.error(errMsg ? errMsg : "服务器错误");
    }
    console.error(err.message, err.response);
    return Promise.reject(err);
  }
);

export default http;
