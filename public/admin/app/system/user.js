import MfDataManage from "comp/mf-data-manage.js";
import { useAuthStore } from "./store/authStore.js";
export default {
  name: "mf-page-user",
  components: {
    MfDataManage,
  },
  data() {
    return {
      user: {},
    };
  },
  mounted() {
    this.user = useAuthStore().user;
  },
  methods: {
    change(action, params) {
      //如果编辑的是当前登录用户，则通知页面更新
      if ("edit" === action && params.id === this.user.id) {
        this.$emit("message", "reload_user_info");
      }
    },
  },
  template: /*html*/ `<div class="mf-page-user">
    <mf-data-manage ref="manageRef" table-name="system:user" @table-data-change="change"></mf-data-manage>
</div>`,
};
