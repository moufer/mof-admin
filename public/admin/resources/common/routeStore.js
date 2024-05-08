import { reactive, ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useRouter } from 'vue-router'

export const useRouteStore = defineStore('route', () => {
    const router = useRouter();

    const rawPerms = ref({})
    const permsList = ref({})

    const currentRoute = ref({})
    const urls = ref([])
    const actions = ref([])

    //面包屑
    const breadcrumbs = computed(() => {
        let id = currentRoute.value?.meta?.id;
        if (id > 0) {
            const path = findPermPathsById(id, permsList.value);
            return path.map(id => permsList.value.find(p => p.id === id).title);
        }
        return [];
    });

    const currentRoutePath = computed(() => {
        return currentRoute.value.path
    });

    function setPerms(data) {
        //设置路由
        addRoutes(data);
        //保存权限
        rawPerms.value = data;
        //把perms（有children的树形结构），转换成无children的扁平列表结构
        permsList.value = flattenPerms(data);
        urls.value = permsList.value.filter(item => item.type === 'menu').map(perm => {
            return { id: perm.id, url: perm.url, 'title': perm.title }
        });
        urls.value.unshift({ id: 0, url: '/index', 'title': '首页' });
        actions.value = permsList.value.filter(item => item.type === 'action');
    }

    //扁平化perms
    function flattenPerms(perms, pid = 0) {
        const result = [];
        for (const perm of perms) {
            const { children, ...rest } = perm;
            result.push({ ...rest, pid });
            if (children && Array.isArray(children)) {
                result.push(...flattenPerms(children, rest.id));
            }
        }
        return result;
    }

    //找perms的path
    function findPermPathsById(id, perms_) {
        const path = [];
        const perm = perms_.find(p => p.id === id);

        if (perm) {
            path.unshift(perm.id);
            if (perm.pid !== 0) {
                const parentPath = findPermPathsById(perm.pid, perms_);
                path.unshift(...parentPath);
            }
        }
        return path;
    }

    //添加路由
    function addRoutes(perms, params = {}) {
        perms.forEach(perm => {
            if (perm.type === 'menu') {
                router.addRoute('frame', {
                    path: perm.url,
                    meta: { title: perm.title, id: perm.id, pid: perm.pid },
                    component: () => import(`../../app/${perm.url}.js`)
                });
            } else if (perm.type === 'group' && perm.children.length > 0) {
                addRoutes(perm.children, params);
            }
        });
    }

    return {
        rawPerms, permsList, currentRoute, currentRoutePath, urls, actions, router,
        breadcrumbs,
        setPerms
    }
})