import { createRouter, createWebHashHistory } from 'vue-router';
import { useRouteStore } from 'comm/routeStore.js';

const mofRouter = function (module, extraRoutes = []) {
    let routes = [{
        name: 'login',
        path: '/login',
        meta: { title: '登录' },
        component: () => import(`../../app/system/login.js`),
    }, {
        name: 'frame',
        path: '/',
        meta: { title: '磨锋管理系统' },
        component: () => import(`../../app/${module}/common/layout.js`),
        children: [{
            name: '404',
            path: '404',
            meta: { title: '页面不存在' },
            component: () => import(`../../app/common/404.js`)
        }, {
            name: 'permission',
            path: 'permission',
            meta: { title: '权限不足' },
            component: () => import(`../../app/common/permission.js`)
        }]
    }];

    //合并路由 routes,extraRoutes
    if (extraRoutes.length > 0) {
        routes = routes.concat(extraRoutes);
    }
    const router = createRouter({
        routes,
        history: createWebHashHistory()
    });

    router.beforeEach(async (to, from) => {
        console.log('beforeEach', from.path, '===>', to.path, '===>', to.meta);
        if (to.meta.title) {
            document.title = to.meta.title + ' - 磨锋管理系统';
        }
        const store = useRouteStore();
        store.$patch({ currentRoute: to })
        //routeStore.getCurrentRoute(to);
    });

    router.onerror = (error) => {
        console.log('router error', error);
    }

    //获取第一个路由
    router.firstRoute = function () {
        const routes = this.getRoutes();
        console.log('routes', routes);
        //遍历routes，找path是/system/开头的
        let result = null;
        for (let i = 0; i < routes.length; i++) {
            const route = routes[i];
            if (route.meta.default || route.path.indexOf('/system/') >= 0) {
                result = route;
                break;
            }
        }
        return result;
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
    }

    return router;
};

export { mofRouter };