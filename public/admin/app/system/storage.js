import MfDataManage from "comp/mf-data-manage.js";
export default {
  components: {
    MfDataManage,
  },
  template: /*html*/ `
    <div class="system-storage">
        <mf-data-manage ref="manageRef" table-name="system:storage" />
    </div>
`,
};
