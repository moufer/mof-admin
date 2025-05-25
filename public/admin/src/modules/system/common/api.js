import http from "/src/utils/http.js";

const passport = {
  login: (key) => http.post("/system/weiqing/login", { key }),
  info: (source) =>
    http.get(`/system/passport/token${window.serverUrlSplit}source=${source}`),
  logout: () => http.post(`/system/passport/logout`),
  perms: () => http.get(`/system/passport/perms`),
};

const client = {
  config: () => http.get("/system/client/config"),
};

export default {
  passport,
  client,
};
