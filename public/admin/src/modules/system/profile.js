import MfDataManage from "/src/components/mf-data-manage.js";
import MfFormRender from "/src/components/mf-form-render.js";
import { formDefaultValue } from "/src/utils/index.js";
export default {
  components: {
    MfDataManage,
    MfFormRender,
  },
  inject: ["http"],
  data() {
    return {
      formItems: [],
      formAttrs: {},
      model: { name: "", email: "" },
      loading: false,
      activeName: "info",
      error: "",
    };
  },
  mounted() {
    this.getFormData();
  },
  methods: {
    getFormData() {
      //从后端获取用户信息
      this.http
        .get(`/system/profile/edit`)
        .then((res) => {
          this.formItems = res.data.elements;
          this.formAttrs = res.data.form;
          this.fillDefaultValue();
        })
        .catch((err) => this.$message.error(err.errmsg || "获取用户信息失败"));
    },
    //填充默认值
    fillDefaultValue: function () {
      this.formItems.forEach((item) => {
        if (typeof item.value !== "undefined") {
          this.model[item.prop] = item.value;
          delete item.value;
        } else if (typeof item._defaultValue !== "undefined") {
          this.model[item.prop] = item._defaultValue;
        } else {
          this.model[item.prop] = formDefaultValue(item.type);
        }
      });
    },
    //通过表达式，判断表单项目是否显示
    showItem(expStr) {
      return expStr ? evaluateExpression(expStr, this.model) : true;
    },
    submitForm() {
      console.log("this.$refs.formRef", this.$refs.formRef);
      this.$refs.formRef.validate((valid) => {
        if (!valid) {
          this.$message.error("表单验证失败");
        } else {
          this.http
            .put("/system/profile", this.model)
            .then((res) => {
              this.$message.success("提交成功");
            })
            .catch((err) => {
              err.errmsg && this.$message.error(err.errmsg);
            });
        }
      });
    },
  },
  template: /*html*/ `
    <div class="mf-page-user data-manage">
    <el-tabs v-model="activeName" type="card">
        <el-tab-pane label="个人资料" name="info">
            <el-row class="table-box" style="borderTopWidth: 0;">
                <el-col :xs="24" :sm="22" :md="20" :lg="16">
                	<el-result icon="error" title="错误提示" :sub-title="error" v-if="error" />    
					<el-form ref="formRef" :model="model" labelWidth="100px" :rules="formAttrs.rules">
						<template v-for="item in formItems" :key="item.prop">
							<mf-form-render v-if="showItem(item._visible)" :item="item" scene="edit" 
								v-model="model[item.prop]"></mf-form-render>
						</template>
						<el-form-item label="&nbsp;">
							<el-button type="primary" size="large" @click="submitForm" 
								:loading="loading">提交</el-button>
						</el-form-item>
                    </el-form>
                </el-col>
            </el-row>
        </el-tab-pane>
        <el-tab-pane label="登录日志" name="log">
			<div class="table-box" style="borderTopWidth: 0;" v-if="activeName==='log'">
            	<mf-data-manage ref="manageRef" table-name="system:admin_login_log"></mf-data-manage>
			<div>
        </el-tab-pane>
    </<el-tabs>
    </div>`,
};
