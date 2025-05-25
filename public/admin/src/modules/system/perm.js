import MfDataManage from "/src/components/mf-data-manage.js";
export default {
  name: "mf-page-perm",
  components: {
    MfDataManage,
  },
  emit: ["message"],
  data() {
    return {};
  },
  methods: {
    change() {
      //重新加载表格字段
      //this.$refs.manageRef.refresh();
      //向主框架发送重新获取权限的消息
      this.$emit("message", "reload_user_info");
    },
  },
  template: /*html*/ `<div class="mf-perm">
    <mf-data-manage ref="manageRef" table-name="system:perm" @table-data-change="change"></mf-data-manage>
</div>`,
};
