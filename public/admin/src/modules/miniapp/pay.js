import MfFormRender from "/src/components/mf-form-render.js";
export default {
  components: {
    MfFormRender,
  },
  inject: ["http", "miniapp", "apiRoot"],
  data() {
    return {
      active: 0,
      formRules: {},
      formValues: {},
      formItems: [],
      loading: false,
      error: "",
      position: "right",
    };
  },
  created() {
    let url = this.apiRoot + "/pay/form";
    this.http
      .get(url)
      .then((res) => {
        this.formItems = res.data;
        this.setFormDefaultValues(res.data);
      })
      .catch((err) => {
        //this.$message.error(err);
        this.error = err.errmsg;
      });
  },
  mounted() {
    window.addEventListener("resize", () => {
      if (window.innerWidth < 768 && this.position !== "top") {
        this.position = "top";
      } else if (window.innerWidth >= 768 && this.position !== "right") {
        this.position = "right";
      }
    });
  },
  methods: {
    setFormDefaultValues(items) {
      items.forEach((item) => {
        this.formValues[item.prop] = item.value || ""; //赋默认值
        delete item.value; //删除默认值
      });
    },
    handSubmit() {
      this.$refs.formEl.validate((valid, fields) => {
        if (!valid) {
          this.$message.error("表单验证失败");
          return;
        }
        //提交
        this.loading = true;
        let url = this.apiRoot + "/pay/submit";
        this.http
          .post(url, this.formValues)
          .then((res) => {
            this.loading = false;
            this.$message.success("保存成功");
          })
          .catch((err) => {
            console.log(err);
            this.$message.error(err.errmsg);
            this.loading = false;
          });
      });
    },
  },
  template: /*html*/ `<div class="page-package">
    <el-card shadow="hover">
        <template #header>微信支付配置(v3)</template>
        <el-result icon="error" title="错误提示" :sub-title="error" v-if="error" />
        <el-form ref="formEl" label-width="150px" :label-position="position"
            :model="formValues" v-if="formItems.length>0">
            <el-row>
                <el-col :xs="24" :sm="22" :md="20" :lg="19" :xl="17">
                    <template  v-for="item in formItems" :key="item.prop">
                        <mf-form-render :item="item" v-model="formValues[item.prop]"></mf-form-render>
                    </template>
                    <el-form-item>
                        <el-button type="primary" @click="handSubmit" :loading="loading">保存</el-button>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
    </el-card>
</div>`,
};
