import { ref, computed, watch } from "vue";
import { ElMessage } from "element-plus";

export default {
  props: {
    name: {
      type: String,
      default: "system",
    },
    path: {
      type: String,
      default: "",
    },
    menus: {
      type: Array,
      default: [],
    },
    urls: {
      type: Array,
      default: [],
    },
    defaultPath: {
      type: String,
      default: "",
    },
    defaultCollapse: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["click-menu", "change-path", "change-collapse"],
  setup(props, { emit }) {
    const menus = computed(() => props.menus);
    const defaultPath = computed(() => props.defaultPath);
    const storageKey = computed(() => `${props.name}_menu_collapse`);
    const isCollapse = ref(localStorage.getItem(storageKey.value) === "1");
    const menuBoxRef = ref(null);

    const handleClick = (event) => {
      if (!event.index) {
        ElMessage.error("菜单不存在");
      }
    };

    const handelCollapseMenu = () => {
      isCollapse.value = !isCollapse.value;
      localStorage.setItem(storageKey.value, isCollapse.value ? 1 : 0);
    };

    const handleScroll = (event) => {
      event.preventDefault();
      //console.log('xxx', $refs.menuBoxRef.scrollTop, event.deltaY);
      menuBoxRef.value.scrollTop += event.deltaY;
    };

    watch(isCollapse, (newValue) => emit("change-collapse", newValue), {
      immediate: true,
    });

    return {
      menuBoxRef,
      menus,
      isCollapse,
      handleClick,
      handelCollapseMenu,
      handleScroll,
    };
  },

  template: /*html*/ `
    <div class="menu-box" ref="menuBoxRef" v-on:wheel="handleScroll">
        <el-menu router class="menu-component" :unique-opened="true" :default-active="defaultPath" 
            background-color="#393d4a" text-color="#fff" active-text-color="#ffd04b" :collapse="isCollapse">
            <template v-for="(menu, index) in menus">
            <el-sub-menu :index="menu.id.toString()" v-if="menu.type=='group'">
                <template #title>
                <el-icon>
                    <component :is="menu.icon ? menu.icon : 'Sugar'"></component>
                </el-icon>
                <span>{{menu.title}}</span>
                </template>
                <template v-for="(menuL2, indexL2) in menu.children">
                <el-menu-item :index="'/'+menuL2.url" @click="handleClick">
                    <template #title>
                    <el-icon>
                        <component :is="menuL2.icon ? menuL2.icon : 'Sugar'"></component>
                    </el-icon>
                    <span>{{menuL2.title}}</span>
                    </template>
                </el-menu-item>
                </template>
            </el-sub-menu>
            <el-menu-item @click="handleClick" :index="'/'+menu.url" v-else-if="menu.type=='menu'">
                <el-icon>
                    <component :is="menu.icon ? menu.icon : 'Sugar'"></component>
                </el-icon>
                <span>{{menu.title}}</span>
            </el-menu-item>
            </template>
        </el-menu>
    </div>
    <div class="menu-fold">
        <el-icon color="#fff" @click="handelCollapseMenu" style="cursor:pointer;">
            <component :is="isCollapse ? 'Expand' : 'Fold'"></component>
        </el-icon>
    </div>
    `,
};
