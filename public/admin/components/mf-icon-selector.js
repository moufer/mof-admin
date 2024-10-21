import * as ElementPlusIconsVue from "../resources/libraries/element-plus-icons-vue@2.1.0/index.js";
export default {
  data() {
    return {
      iconsDrawer: false,
      iconAll: [],
      icons: [],
      iconProp: "",
      keyword: "",
    };
  },
  props: {
    item: {
      type: Object,
      default: () => {
        return {};
      },
    },
    modelValue: {
      type: String,
      default: "",
    },
  },
  emits: ["update:modelValue"],
  computed: {
    value: {
      get: function () {
        return this.modelValue;
      },
      set: function (newValue) {
        this.$emit("update:modelValue", newValue);
      },
    },
  },
  watch: {
    keyword: function (val, oldVal) {
      this.icons = this.iconAll.filter(
        (item) => item.toLowerCase().includes(val.toLowerCase()) || val === ""
      );
    },
  },
  created() {
    if (this.icons.length === 0) {
      //从elementPlus里icon获取图标列表
      for (const [key] of Object.entries(ElementPlusIconsVue)) {
        this.icons.push(key);
        this.iconAll.push(key);
      }
    }
  },
  methods: {
    openIconsDrawer() {
      this.iconsDrawer = true;
    },
    closeIconsDrawer(icon) {
      if (icon) {
        this.value = icon;
      }
      this.iconsDrawer = false;
    },
  },
  template: /*html*/ `
  <div class="iconSelector" style="width:100%;display:flex;">
    <el-col :span="9">
        <el-input v-model="value" autocomplete="off"></el-input>
    </el-col>
    <el-col :span="1" :offset="1" style="text-align: center;"> 
        <el-button icon="Search" @click="iconsDrawer=true">选择</el-button>
    </el-col>
    <el-drawer
        v-model="iconsDrawer"
        title="图标选择"
        direction="rtl"
        :lock-scroll="false">
        <el-row :gutter="10" style="margin-bottom:10px;">
            <el-col :span="24" style="text-align: center;">
                <el-input v-model="keyword" placeholder="搜索图标"></el-input>
            </el-col>
        </el-row>
        <el-row :gutter="10">
            <el-col :span="4" v-for="icon in icons" :key="icon" style="text-align: center;">
                <el-button link @click="closeIconsDrawer(icon)" style="font-size: 20px;" :title="icon">
                    <el-icon><component :is="icon"></component></el-icon>
                </el-button>
            </el-col>
        </el-row>
    </el-drawer>
  </div>
  `,
};
