window.process = { env: { NODE_ENV: "production" } };
window.__VUE_PROD_DEVTOOLS__ = false;

window.clientUrl = window.location.href.replace(/\/[^/]*([?#].*)?$/, "");
//去掉clientUrl里的最后一级目录
window.serverUrl = window.clientUrl.replace(/\/[^/]*([?#].*)?$/, "");
//api域名
window.serverUrl = "http://127.0.0.1:8000";
