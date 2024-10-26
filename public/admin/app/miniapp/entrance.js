import MfDataManage from "comp/mf-data-manage.js";
export default {
  components: {
    MfDataManage,
  },
  inject: ["miniapp"],
  data() {
    return {
      configUrl: "",
      operations: {
        label: "操作",
        show: true,
        width: 150,
        mode: "text",
        buttons: [
          {
            label: "复制",
            name: "manage",
            click: (row) => {
              //复制到剪贴板
              if (navigator.clipboard) {
                navigator.clipboard
                  .writeText(row.url)
                  .then(() => {
                    this.$message.success("复制成功");
                  })
                  .catch((err) => {
                    console.log(err);
                    this.$message.error("复制失败");
                  });
              } else {
                //提示不支持复制到剪贴板
                this.$message.warning(
                  "您的浏览器不支持复制到剪贴板，请手动复制链接"
                );
              }
            },
          },
        ],
      },
    };
  },
  created() {
    this.configUrl = `/system/table/{tableName}?id=${this.miniapp.id}`;
  },
  template: /*html*/ `
  <div class="page-entrance">
    <mf-data-manage ref="manageRef" 
        table-name="miniapp:entrance" 
        :table-config-url="configUrl"
        :table-column-operations="operations"></mf-data-manage>
  </div>
  `,
};
