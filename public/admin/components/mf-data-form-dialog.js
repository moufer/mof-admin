import { evaluateExpression, deepCopy, formDefaultValue } from "utils";
import { isEmpty } from "lodash";
import MfFormRender from "comp/mf-form-render.js";
export default {
  name: "mf-data-form-dialog",
  components: {
    MfFormRender,
  },
  emits: ["submit"],
  data() {
    return {
      visible: false,
      action: "",
      error: "",
      originalModel: {}, //初始表单内容值
      model: {}, //表单内容值
      dialog: {},
      form: {},
      items: [],
      title: "",
      activeTab: "",
      tabs: [],
      onSubmit: null,
      submitted: false,
    };
  },
  created() {
    this.init();
  },
  watch: {
    visible: function (val) {
      if (val === false) {
        setTimeout(() => this.init(), 100);
      }
    },
  },
  methods: {
    init: function () {
      this.model = {};
      this.originalModel = {};
      this.items = {};
      this.activeTab = "";
      this.tabs = [];
      this.title = "";
      this.dialog = { width: 650 };
      this.form = { labelWidth: "auto" };
      this.action = "";
      this.error = "";
      this.pkId = "";
    },
    //填充默认值
    fillDefaultValue: function () {
      this.items.forEach((item) => {
        if (typeof this.model[item.prop] === "undefined") {
          if (typeof item.value !== "undefined") {
            this.model[item.prop] = item.value;
            delete item.value;
          } else if (typeof item._defaultValue !== "undefined") {
            this.model[item.prop] = item._defaultValue;
          } else {
            this.model[item.prop] = formDefaultValue(item.type);
          }
        }
      });
    },
    //添加
    addDialog(dialog, form, items, options = {}) {
      dialog.title = options.title || dialog.title || "添加";
      dialog.width = options.title || dialog.width || 650;
      this.visible = true;
      this.action = "add";
      this.dialog = dialog;
      this.form = form;
      this.items = this.setElements(items);
      this.fillDefaultValue();
      this.$nextTick(() => {
        setTimeout(() => (this.originalModel = deepCopy(this.model)), 1000);
      });
    },
    //编辑
    editDialog(pkId, dialog, form, items, options = {}) {
      dialog.title = dialog.title || options.title || "编辑";
      dialog.width = dialog.width || options.width || 650;
      this.visible = true;
      this.action = "edit";
      this.dialog = dialog;
      this.form = form;
      this.items = this.setElements(items);
      this.model = this.getValues();
      this.pkId = pkId;
      this.fillDefaultValue();
      this.$nextTick(() => {
        setTimeout(() => (this.originalModel = deepCopy(this.model)), 1000);
      });
    },
    //关闭
    hideDialog() {
      this.visible = false;
    },
    setElements(elements) {
      let result = [];
      if (typeof elements.tabs !== "undefined") {
        elements.tabs.forEach((tab) => {
          this.tabs.push({ prop: tab.prop, label: tab.label });
          tab.elements.forEach((element) => {
            element.tab = tab.prop;
          });
          result.push(...tab.elements);
        });
        this.activeTab = this.tabs[0].prop;
      } else {
        result.push(...elements);
      }
      return result;
    },
    //通过表达式，判断表单项目是否显示
    showItem(expStr) {
      return expStr ? evaluateExpression(expStr, this.model) : true;
    },
    //通过表达式，判断表单项目数据切换
    switchItem(item) {
      if (Array.isArray(item._switch)) {
        item._switch.some((switchItem, index) => {
          const condition = evaluateExpression(switchItem._expr, this.model);
          //表达式成立，替换数据
          if (condition && item._switch_current !== index) {
            if (item._switch_current >= 0) {
              //记录数值，便于切换是赋值
              item._switch[item._switch_current]._lastValue =
                this.model[item.prop];
            }
            Object.keys(switchItem).forEach((key) => {
              //替换数据
              if (!["_expr", "_lastValue"].includes(key)) {
                item[key] = switchItem[key];
              }
            });
            item._switch_current = index; //避免频繁赋值，记录当前序号
            //写入上一次的值
            if (typeof switchItem._lastValue !== "undefined") {
              this.model[item.prop] = switchItem._lastValue;
            } else if (this.action === "add") {
              this.model[item.prop] = item._defaultValue || "";
            }
            return true;
          }
          return false;
        });
      }
      return item;
    },
    getValues() {
      let values = {};
      this.items.forEach((item) => {
        values[item.prop] = deepCopy(item.value);
        delete item.value;
      });
      return values;
    },
    //提交表单
    handleSubmit() {
      let postData = {};
      this.items.forEach((item) => {
        postData[item.prop] = this.model[item.prop];
      });
      //检测item.prop是否存在"."符号，如果存在，则表示是“对象.属性”
      postData = this._convertObject(postData);
      console.log("postData_convertObject", postData);
      //表单验证
      this.$refs.formRef.validate((valid) => {
        if (!valid) {
          this.$message.error("表单验证失败");
        } else {
          //触发提交事件
          this.$emit("submit", this.action, postData, this.pkId);
          this.submitted = true;
        }
      });
    },
    //重制表单内容
    handleReset() {
      this.$refs.formRef.resetFields();
    },
    //点击tab
    handleTabClick(tab, event) {
      console.log(tab);
    },
    //对比数据是否更新
    checkModelChange(checkFields) {
      //对比this.model和this.originalModel里的内容是否有不同
      let change = [];
      Object.keys(this.originalModel).forEach((key) => {
        if (this.originalModel[key] !== this.model[key]) {
          change.push(key);
        }
      });
      console.log("change", change);
      //没有更新
      if (change.length === 0) return false;
      //检测checkFields和checkFields是否有交集
      return (
        isEmpty(checkFields) ||
        checkFields.some((item) => {
          return change.includes(item);
        })
      );
    },
    //关闭时提示
    onBeforeClose(DoneFn) {
      if (!isEmpty(this.dialog.beforeClose)) {
        const options = this.dialog.beforeClose;
        //检测是否需要验证字段的更新
        if (!isEmpty(options.check) && !this.checkModelChange(options.check)) {
          DoneFn();
          return;
        }
        this.$messageBox
          .confirm(this.dialog.beforeClose.message || "您确认关闭吗？", {
            confirmButtonText: "确定",
            cancelButtonText: "取消",
            type: "warning",
          })
          .then(() => DoneFn())
          .catch(() => {});
      } else {
        DoneFn();
      }
    },
    //一层对象转换成多层
    _convertObject(obj) {
      const result = {};
      for (let key in obj) {
        if (obj.hasOwnProperty(key)) {
          if (key.includes(".")) {
            const [parentKey, childKey] = key.split(".");
            result[parentKey] = result[parentKey] || {};
            result[parentKey][childKey] = obj[key];
          } else {
            result[key] = obj[key];
          }
        }
      }
      return result;
    },
  },
  template: /*html*/ `
    <el-dialog v-model="visible" v-bind="dialog" :lock-scroll="false" :before-close="onBeforeClose">
        <template #default>
            <el-result icon="error" title="错误提示" :sub-title="error" v-if="error" />
            <el-form ref="formRef" :model="model" v-bind="form" autocomplete="new-dialog">
                <el-tabs v-model="activeTab" @tab-click="handleTabClick" v-if="tabs.length" type="card">
                    <el-tab-pane :label="tab.label" :name="tab.prop" v-for="tab in tabs">
                        <el-row style="padding:0 10px;" :gutter="10">
                            <template v-for="item in items" :key="item.prop">
                            <el-col :span="item.colSpan||24" v-if="item.tab===tab.prop">
                                <mf-form-render v-if="showItem(item._visible)" :item="switchItem(item)" 
                                    :scene="action" v-model="model[item.prop]"></mf-form-render>
                            </el-col>
                            </template>
                        </el-row>
                    </el-tab-pane>
                </el-tabs>
                <el-row style="padding:0 10px;" :gutter="10" v-else>
                    <template v-for="item in items" :key="item.prop">
                        <el-col :span="item.colSpan||24">
                            <mf-form-render v-if="showItem(item._visible)" :item="switchItem(item)" 
                                :scene="action" v-model="model[item.prop]"></mf-form-render>
                        </el-col>
                    </template>
                </el-row>
            </el-form>
        </template>
        <template #footer>
            <div style="padding:0 10px;">
                <el-button @click="hideDialog" size="large">取消</el-button>
                <el-button type="primary" @click="handleSubmit" size="large">确定</el-button>
            </div>
        </template>
    </el-dialog>
    `,
};
