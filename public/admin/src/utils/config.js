import http from "/src/utils/http.js";

const getConfig = async () => {
  const res = await http.get("/system/client/config");
  return res.data;
};

export { getConfig };
