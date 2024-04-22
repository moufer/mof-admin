import { ref, getCurrentInstance, provide, inject, onMounted, onUnmounted } from 'vue'
import { logout } from 'comm/userLogin.js';
import { addStyle } from 'comm/utils.js';
import MfMenu from 'comp/mf-menu.js';
import { useRouteStore } from 'comm/routeStore.js';
import { useUserStore } from 'comm/userStore.js';
import { storeToRefs } from 'pinia';

export default {
  components: {
    MfMenu
  },
  setup() {

    const instance = getCurrentInstance();

    const { currentRoutePath, breadcrumbs, rawPerms } = storeToRefs(useRouteStore());
    const { router } = useRouteStore()
    const { user } = useUserStore();

    const miniapp = inject('miniapp');

    //组件样式动态添加
    const styleStr = instance.type?.style;
    const styleEl = styleStr ? addStyle(styleStr) : null;
    onUnmounted(() => {
      //移除样式
      styleEl?.remove();
    });

    const apiRoot = ref(`miniapp/backend/${miniapp.value.id}`);
    provide('apiRoot', apiRoot);

    const changePagePath = (path) => {
      console.log('changePagePath', path);
      router.push(`/${path}`);
    }

    const menuCollapsed = ref(true);
    const changeCollapsed = (collapsed) => {
      menuCollapsed.value = collapsed;
    }

    const userLogout = () => {
      logout().then(() => {
        document.location.href = '/miniapp.html';
      });
    }

    onMounted(() => {
      const instance = getCurrentInstance();
      instance.refs.menu.setPagePath(currentRoutePath.value);
    });

    return {
      user,
      rawPerms,
      miniapp,
      breadcrumbs,
      menuCollapsed,
      userLogout,
      changePagePath,
      changeCollapsed
    }
  },

  template: /*html*/`
  <el-container class="body-container">
  <el-aside>
    <div class="miniapp-box" :class="menuCollapsed?'fold':'expand'">
      <div class="miniapp-icon">
        <img class="miniapp-icon-img" :src="miniapp.avatar_img" />
      </div>
      <div class="miniapp-title">{{miniapp.title}}</div>
    </div>
    <mf-menu ref="menu" name="miniapp" :menus="rawPerms" @change-path="changePagePath"
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
                  <el-dropdown-item>资料</el-dropdown-item>
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
  style:/*css*/`
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
  `
}