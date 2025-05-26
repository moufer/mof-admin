import http from "/src/utils/http.js";

const miniapp = {
  read: (id) => http.get(`/miniapp/backend/index/${id}`),
};

export default { miniapp };
