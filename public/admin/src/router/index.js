import { createRouter, createWebHashHistory } from "vue-router";
import { ElMessage } from "element-plus";
import { useAuthStore } from "/src/modules/system/store/authStore.js";
import { usePermStore } from "/src/modules/system/store/permStore.js";

let router = null;

export function getRouter() {
  return router;
}

export function mofRouter(app, module = "system", extraRoutes = []) {
  let routes = [
    {
      name: "frame",
      path: "/",
      meta: { title: window.__SITE_NAME__, requiresAuth: true },
      component: () => import(`/src/modules/${module}/common/frame.js`),
    },
    {
      name: "login",
      path: "/login",
      meta: { title: "登录" },
      component: () => import("/src/modules/system/login.js"),
    },
  ];

  //合并路由 routes,extraRoutes
  if (extraRoutes.length > 0) {
    routes = routes.concat(extraRoutes);
  }

  router = createRouter({
    routes,
    history: createWebHashHistory(),
  });

  let isLoadRoutes = false;
  const permsToRoutes = function (perms, params) {
    perms.forEach((perm) => {
      if (perm.type === "menu") {
        router.addRoute("frame", {
          path: "/" + perm.url,
          meta: {
            title: perm.title,
            id: perm.id,
            pid: perm.pid,
            requiresAuth: true,
          },
          component: () =>
            import(`/src/modules/${perm.url}.js`).catch((err) => {
              console.log(err);
              return import("/src/modules/common/404.js");
            }),
        });
      } else if (perm.type === "group" && perm.children.length > 0) {
        permsToRoutes(perm.children, params);
      }
    });
  };

  router.beforeEach(async (to, from, next) => {
    let notfound = to.matched.length === 0;
    let requiresAuth = to.meta?.requiresAuth || false;
    //检测to是不是存在的规则
    if (notfound) {
      //console.log("notfound", to.path);
      //路由不存在时，可能是登录后才能加载的规则，要求登录
      requiresAuth = true;
    }

    //检测是否要登录才能访问
    if (requiresAuth) {
      const authStore = useAuthStore();
      const permStore = usePermStore();
      //判断是否登录
      if (!authStore.isLogin && !(await authStore.autoLogin())) {
        //未登录，跳转到登录页面
        next("/login");
      }
      //因为动态路由规则是根据用户获取的，所有放在登录检测里
      if (!isLoadRoutes) {
        try {
          const perms = await permStore.loadPerms();
          //添加路由规则
          router.setRouters(perms);
          isLoadRoutes = true;
        } catch (err) {
          err.errmsg && ElMessage.error(err.errmsg);
        }
        //  添加规则后，重新加载
        if (notfound && router.hasRoutePath(to.path)) {
          //console.log("router.hasRoutePath", to.path);
          notfound = false;
          next(to.path);
          return;
        }
      }
    }

    //console.log("beforeEach", from.path, "===>", to.path, to.meta);
    if (to.meta.title) {
      document.title = to.meta.title + " - " + window.__SITE_NAME__;
    }

    if (notfound) {
      next("/404");
    } else {
      next();
    }
  });

  router.onerror = (error) => {
    console.log("router error", error);
  };

  //检测路由path是否存在
  router.hasRoutePath = function (path) {
    const routes = this.getRoutes();
    for (let i = 0; i < routes.length; i++) {
      const route = routes[i];
      if (route.path === path) {
        return true;
      }
    }
    return false;
  };

  router.setRouters = function (perms) {
    //重置路由规则
    // this.getRoutes().forEach((route) => {
    //   //console.log(route);
    // });
    console.log("加载路由规则");
    permsToRoutes(perms);
  };

  app.use(router);

  return router;
}
