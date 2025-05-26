import MfDataManage from "/src/components/mf-data-manage.js";
export default {
  name: "mf-page-admin",
  components: {
    MfDataManage,
  },
  template: /*html*/ `<div class="mf-page-user">
    <mf-data-manage ref="manageRef" table-name="miniapp:admin"></mf-data-manage>
</div>`,
};
