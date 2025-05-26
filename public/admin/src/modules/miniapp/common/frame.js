import {
  ref,
  provide,
  onMounted,
  onUnmounted,
  computed,
  getCurrentInstance,
} from "vue";
import { useRoute, useRouter } from "vue-router";

import { addStyle } from "/src/utils/index.js";
import { useAuthStore } from "/src/modules/system/store/authStore.js";
import { usePermStore } from "/src/modules/system/store/permStore.js";
import { useMiniappStore } from "/src/modules/miniapp/store/miniappStore.js";
import MfMenu from "/src/components/mf-menu.js";

export default {
  components: { MfMenu },
  setup() {
    const id = new URLSearchParams(window.location.search).get("id");

    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const miniappStore = useMiniappStore();
    const permStore = usePermStore();
    const instance = getCurrentInstance();

    const collapsed = ref(true);

    const miniapp = computed(() => miniappStore.miniapp);
    const apiRoot = computed(() => `miniapp/backend/${id}`);

    const user = computed(() => authStore.user);
    const path = computed(() => route.path);
    const menus = computed(() => permStore.perms);
    const urls = computed(() => permStore.urls);

    //模块管理首页
    const firstPath = computed(() => urls.value.find((u) => u.id > 0));
    //面包屑
    const breadcrumbs = computed(() => permStore.breadcrumbs(route.meta?.id));

    //组件样式动态添加
    const styleStr = instance.type?.style;
    const styleEl = styleStr ? addStyle(styleStr) : null;
    onUnmounted(() => styleEl?.remove());

    const changeCollapsed = (status) => (collapsed.value = status);

    const userLogout = async () => {
      await authStore.logout();
      document.location.href = "/miniapp.html";
    };

    onMounted(async () => {
      if (!id) {
        document.location.href = "/miniapp.html";
        return;
      }
      if (route.path === "/") {
        router.replace(firstPath.value.url);
      }
    });

    provide("miniapp", miniapp);
    provide("apiRoot", apiRoot);

    return {
      user,
      menus,
      path,
      miniapp,
      breadcrumbs,
      collapsed,
      userLogout,
      changeCollapsed,
    };
  },

  template: /*html*/ `
    <el-container class="body-container">
      <el-aside>
        <div class="miniapp-box" :class="collapsed?'fold':'expand'">
          <div class="miniapp-icon">
            <img class="miniapp-icon-img" :src="miniapp.avatar_img" />
          </div>
          <div class="miniapp-title">{{miniapp.title}}</div>
        </div>
        <mf-menu
          name="miniapp" 
          :default-path="path"
          :menus="menus"
          :urls="urls"
          @change-collapse="changeCollapsed"></mf-menu>
      </el-aside>
      <el-main>
        <div class="mf-page">
          <div class="page-title">
            <div class="page-breadcrumb">
              <el-breadcrumb separator="/">
                <el-breadcrumb-item><a href="miniapp.html#/index">平台首页</a></el-breadcrumb-item>
                <el-breadcrumb-item v-for="breadcrumb in breadcrumbs">{{breadcrumb}}</el-breadcrumb-item>
              </el-breadcrumb>
            </div>
            <div class="page-user">
              <div class="avatar-box">
                <img class="avatar-img" :src="user.avatar" />
                <el-dropdown>
                  <span class="user-name">
                    {{user.name||user.username}}
                    <el-icon>
                      <arrow-down />
                    </el-icon>
                  </span>
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item @click="userLogout">登出</el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </div>
            </div>
          </div>
          <router-view v-slot="{ Component }">
            <transition name="page">
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </el-main>
    </el-container>
    `,

  style: /*css*/ `
      .page-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 0 5px;
        height: 40px;
      }
      .page-user {
        display: flex;
        align-items: center;
        font-size: 14px;
        height: 40px;
      }
      .avatar-box {
        height: 30px;
        text-align: center;
        cursor: pointer;
        display: flex;
        flex-direction: row;
        align-items: center;
  
      }
      .avatar-img {
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: inline-block;
        margin-right: 5px;
      }
  
      .avatar-img span {
        margin-left: 5px;
        cursor: pointer;
        color: var(--el-color-white);
        outline: none;
        display: flex;
        align-items: center;
      }
    `,
};
