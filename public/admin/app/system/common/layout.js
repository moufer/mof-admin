import { ref, getCurrentInstance, onMounted } from 'vue'
import { storeToRefs } from 'pinia';
import { logout } from 'comm/userLogin.js';
import MfMenu from 'comp/mf-menu.js';
import { useRoute } from 'vue-router';
import { useRouteStore } from 'comm/routeStore.js';
import { useUserStore } from 'comm/userStore.js';

export default {
  components: {
    MfMenu
  },
  setup() {

    const { currentRoutePath, rawPerms } = storeToRefs(useRouteStore());
    const { router } = useRouteStore()
    const { user } = useUserStore();

    const menuCollapsed = ref(true);

    const changePagePath = (path) => {
      router.push(`/${path}`);
    }

    const changeCollapsed = (collapsed) => {
      menuCollapsed.value = collapsed;
    }

    const userLogout = () => {
      logout().then(() => {
        router.push('/login');
      });
    }

    onMounted(() => {
      const instance = getCurrentInstance();
      const route = useRoute();
      if (route.path !== currentRoutePath.value) {
        instance.refs.menu.setPagePath(currentRoutePath.value);
      } else {
        console.log('passed', currentRoutePath.value);
      }
    });

    return {
      user,
      rawPerms,
      menuCollapsed,
      userLogout,
      changePagePath,
      changeCollapsed
    }
  },
  template: /*html*/`
    <el-container class="body-container">
      <el-header>
        <div class="logo-box">MofAdmin</div>
        <div class="navs-bar"></div>
        <div class="status-bar">
          <div class="avatar-box">
            <img class="avatar-img" :src="user.avatar" />
            <el-dropdown>
              <span class="user-name">
                {{user.username}}
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
      </el-header>
      <el-container class="iframe-container">
        <el-aside>
          <mf-menu ref="menu" name="system" :menus="rawPerms" @change-path="changePagePath"
          @change-collapse="changeCollapsed"></mf-menu>
        </el-aside>
        <el-main>
          <div class="mf-page">
            <router-view v-slot="{ Component }">
              <transition name="page">
                <component :is="Component" />
              </transition>
            </router-view>
          </div>
        </el-main>
      </el-container>
    </el-container>
    `
}