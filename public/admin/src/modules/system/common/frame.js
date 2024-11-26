import { ref, onMounted, watch, computed, inject } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "/src/modules/system/store/authStore.js";
import { usePermStore } from "/src/modules/system/store/permStore.js";
import MfMenu from "/src/components/mf-menu.js";

export default {
  components: {
    MfMenu,
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const permStore = usePermStore();

    const user = computed(() => authStore.user);
    const menus = computed(() => permStore.perms);
    const urls = computed(() => permStore.urls);
    const path = computed(() => route.path);

    const menuCollapsed = ref(true);
    const menuRef = ref(null);

    const siteName = inject("siteName");

    const changeCollapsed = (collapsed) => (menuCollapsed.value = collapsed);

    const userLogout = async () => {
      await authStore.logout();
      router.push("/login");
    };

    const toProfile = () => router.push("/system/profile");

    watch(route.path, (path) => {
      console.log("path", path);
    });

    onMounted(() => {
      if (route.path === "/") {
        //取到第一个菜单
        const url = urls.value.find((u) => u.id > 0);
        if (url) router.replace(url.url);
      }
    });

    return {
      menuRef,
      siteName,
      path,
      user,
      urls,
      menus,
      menuCollapsed,
      toProfile,
      userLogout,
      changeCollapsed,
    };
  },

  template: /*html*/ `
    <el-container class="body-container">
      <el-header>
        <div class="logo-box">{{siteName}}</div>
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
                  <el-dropdown-item @click="toProfile">资料</el-dropdown-item>
                  <el-dropdown-item @click="userLogout">登出</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </div>
      </el-header>
      <el-container class="iframe-container">
        <el-aside>
          <mf-menu ref="menuRef" name="system" 
          :default-path="path"
          :menus="menus"
          :urls="urls"
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
    `,
};
