import MfDataManage from "/src/components/mf-data-manage.js";
export default {
  name: "mf-page-role",
  components: {
    MfDataManage,
  },
  template: /*html*/ `<div class="mf-dynamic">
    <mf-data-manage ref="manageRef" table-name="system:role"></mf-data-manage>
</div>`,
};
