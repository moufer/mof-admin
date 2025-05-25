import { computed, watch, ref, getCurrentInstance } from "vue";
import { cloneDeep, isNil, isEqual } from "lodash-es";
import { ElMessage } from "element-plus";

import { isEmpty } from "/src/utils/index.js";
import MfKeyValue from "/src/components/mf-key-value.js";
import MfXselect from "/src/components/mf-xselect.js";
import MfIconSelector from "/src/components/mf-icon-selector.js";
import MfInputDict from "/src/components/mf-input-dict.js";
import MfSelectSearch from "/src/components/mf-select-search.js";
import MfUpload from "/src/components/mf-upload.js";
import MfEditor from "/src/components/mf-ueditor.js";
import MfPage from "/src/components/mf-page.js";
import MfCombEditor from "/src/components/mf-comb-editor.js";

const commProps = {
  item: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: [Object, Array, String, Number, Boolean],
    default: "",
  },
};

const commSetup = (props, { emit }) => {
  const formValue = ref(props.modelValue);
  const updateModelValue = (value) => {
    emit("update:modelValue", value);
  };
  watch(
    () => props.modelValue,
    (val) => (formValue.value = val)
  );
  return {
    formValue,
    updateModelValue,
  };
};

const commOptions = {
  props: { ...commProps },
  emits: ["update:modelValue"],
  setup: (props, ctx) => commSetup(props, ctx),
};

const MfComponentHub = {
  props: { ...commProps },
  emits: ["update:modelValue"],
  components: {
    MfKeyValue,
    MfXselect,
    MfIconSelector,
    MfInputDict,
    MfSelectSearch,
    MfUpload,
    MfEditor,
    MfCombEditor,
  },
  tags: {
    cascader: "el-",
    rate: "el-",
    "color-picker": "el-",
    slider: "el-",
    transfer: "el-",
    "input-number": "el-",
    autocomplete: "el-",
    "key-value": "mf-",
    xselect: "mf-",
    "icon-selector": "mf-",
    "input-dict": "mf-",
    "select-search": "mf-",
    upload: "mf-",
    editor: "mf-",
    "comb-editor": "mf-",
  },
  setup(props, { emit }) {
    const formValue = ref(props.modelValue);
    const component = ref("");
    const subType = ref("");
    component.value = MfComponentHub.getComponent(props.item.type);
    //检测是否存在subType
    if (props.item.type.indexOf(":") > -1) {
      subType.value = props.item.type.split(":")[1];
    }
    watch(
      () => props.modelValue,
      (val) => (formValue.value = val)
    );
    return {
      formValue,
      component,
      subType,
    };
  },
  isCommonlyTag: (tag) => {
    //检测tag是否存在":"
    if (tag.indexOf(":") > -1) {
      tag = tag.split(":")[0];
    }
    //获取tags的keys
    return Object.keys(MfComponentHub.tags).includes(tag);
  },
  getComponent: (tag) => {
    //检测tag是否存在":"
    if (tag.indexOf(":") > -1) {
      tag = tag.split(":")[0];
    }
    let tagName = MfComponentHub.tags[tag] || "";
    return `${tagName}${tag}`;
  },
  template: /*html*/ `
    <component :is="component" v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)" 
    v-bind="item" :sub-type="subType"></component>
    `,
};

const MfLabel = {
  ...commOptions,
  template: /*html*/ `
    <span>{{modelValue}}</span>
    `,
};

const MfHtml = {
  ...commOptions,
  template: /*html*/ `
    <div v-html="item.content"></div>
    `,
};

const MfImage = {
  ...commOptions,
  template: /*html*/ `
    <img :src="modelValue" style="max-width:100%;" :referrerPolicy="item.referrerPolicy||'no-referrer-when-downgrade'" />
    `,
};

const MfInput = {
  ...commOptions,
  template: /*html*/ `
    <el-input v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)"
        autocomplete="off" v-bind="item">
        <template v-if="item.prepend" #prepend>{{item.prepend}}</template>
        <template v-if="item.append" #append>{{item.append}}</template>
    </el-input>
    `,
};

const MfUploadRaw = {
  props: { ...commProps },
  emits: ["update:modelValue"],
  setup(props, { emit }) {
    const instance = getCurrentInstance();
    if (typeof props.item.headers === "undefined") {
      props.item.headers = {
        Authorization: localStorage.getItem("admin_token"),
      };
    }
    const updateItemValue = (newValue) => emit("update:modelValue", newValue);
    const fileList = ref([]);
    const showUploadBtn = computed(() => {
      return fileList.value.length < props.item.limit;
    });

    if (isEmpty(props.modelValue)) {
      updateItemValue([]);
    } else {
      fileList.value = cloneDeep(props.modelValue);
    }

    props.item.accept = props.item.accept || "*/*";
    props.item.listType = props.item.listType || "text";
    props.item.multiple = props.item.limit > 1;
    props.item.showFileList = true;
    props.item.data = { extra: props.item.prop };

    const beforeUpload = (rawFile) => {
      let size = rawFile.size; //字节
      let ext = rawFile.name.split(".").pop().toLowerCase(); //后缀
      if (!isEmpty(props.item.limitExt)) {
        //扩展名
        let exts = props.item.limitExt
          .split(",")
          .map((item) => item.toLowerCase());
        if (!exts.includes(ext)) {
          ElMessage.error(`不支持此文件格式`);
          return false;
        }
      }
      if (props.item.limitSize > 0 && size > props.item.limitSize) {
        //大小
        ElMessage.error(`文件大小超过了最大限制`);
        return false;
      }
      return true;
    };

    const uploadExceed = (files, fileList, e) => {
      if (props.item.limit === 1) {
        //替换
        instance.refs.upload.clearFiles();
        instance.refs.upload.handleStart(files[0]);
        instance.refs.upload.submit();
      } else {
        ElMessage.error(`超出了上传数量`);
      }
    };

    const uploadSuccess = (res, file, fileList) => {
      if (res.errcode !== 0) {
        instance.refs.upload.handleRemove(file);
        ElMessage({ message: res.errmsg, type: "error" });
      } else {
        let formValue = [];
        if (fileList.length > 0) {
          formValue = fileList.map((item) => {
            return { path: item.response?.data.path };
          });
        }
        updateItemValue(formValue);
      }
    };

    const uploadRemove = (uploadFile, uploadFiles) => {
      let formValue = [];
      if (uploadFiles.length > 0) {
        formValue = uploadFiles.map((item) => {
          return { path: item.response?.data.path };
        });
      }
      updateItemValue(formValue);
    };

    const previewImageDialogImageUrl = ref("");
    const previewImageDialogVisible = ref(false);
    const uploadPreview = (file) => {
      previewImageDialogImageUrl.value = file.url;
      previewImageDialogVisible.value = true;
    };

    const uploadError = (error, uploadFile, uploadFiles) => {
      ElMessage({
        message: error.errmsg || "上传失败",
        type: "error",
      });
    };
    return {
      fileList,
      showUploadBtn,
      previewImageDialogImageUrl,
      previewImageDialogVisible,
      beforeUpload,
      uploadExceed,
      uploadSuccess,
      uploadRemove,
      uploadPreview,
      uploadError,
    };
  },
  template: /*html*/ `
    <el-upload ref="upload" v-model:file-list="fileList" v-bind="item"
        :before-upload="beforeUpload" :on-exceed="uploadExceed" :on-remove="uploadRemove"
        :on-success="uploadSuccess" :on-error="uploadError" :on-preview="uploadPreview">
        <el-icon v-if="item.type==='upload:image'" class="picture-uploader-icon"><Plus /></el-icon>
        <el-button type="primary" v-else>上传文件</el-button>
    </el-upload>
    <el-dialog v-model="previewImageDialogVisible" v-if="item.type==='upload:image'">
        <img style="max-width:100%;" :src="previewImageDialogImageUrl" alt="图片预览" />
    </el-dialog>
    `,
};

const MfSwitch = {
  props: { ...commProps },
  emits: ["update:modelValue"],
  setup(props, { emit }) {
    const formValue = ref(props.modelValue);
    watch(
      () => props.modelValue,
      (val) => (formValue.value = val)
    );
    props.item["active-color"] = props.item["active-color"] || "#13ce66";
    props.item["inactive-color"] = props.item["inactive-color"] || "#ff4949";
    return {
      formValue,
    };
  },
  template: /*html*/ `
    <el-switch v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)" 
        v-bind="item"></el-switch>
    `,
};

const MfSelect = {
  ...commOptions,
  template: /*html*/ `
    <el-select v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)" 
        v-bind="item">
        <el-option v-for="option in item.options" :key="option.value" v-bind="option"></el-option>
    </el-select>
    `,
};

const MfSelectMultiple = {
  ...commOptions,
  template: /*html*/ `
    <el-select v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)" 
        v-bind="item" multiple>
        <el-option v-for="option in item.options" :key="option.value" v-bind="option"></el-option>
    </el-select>
    `,
};

const MfRadio = {
  ...commOptions,
  template: /*html*/ `
    <el-radio-group v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)" 
        v-bind="item">
        <el-radio v-for="option in item.options" v-bind="option"
            >{{option.label}}</el-radio>
    </el-radio-group>
    `,
};

const MfCheckbox = {
  ...commOptions,
  template: /*html*/ `
    <el-checkbox v-if="item.single" 
        v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)"
        v-bind="item.option">{{item.option.caption}}</el-checkbox>
    <el-checkbox-group v-else 
        v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)">
        <el-checkbox v-for="option in item.options" 
            v-bind="option">{{option.caption}}</el-checkbox>
    </el-checkbox-group>
    `,
};

const MfTree = {
  props: { ...commProps },
  emits: ["update:modelValue"],
  setup(props, { emit }) {
    const instance = getCurrentInstance();
    const defaultCheckedKeys = JSON.stringify(
      isNil(props.modelValue) || !props.modelValue.length
        ? []
        : props.modelValue
    );
    console.log("tree", props.item);
    props.item.showCheckbox = props.item.showCheckbox || true;
    props.item.props = props.item.props || {
      label: "label",
      children: "children",
    };
    props.item.defaultCheckedKeys = JSON.parse(defaultCheckedKeys);
    props.item.onCheck = (name, checked) => {
      //const checkedKeys = checked.checkedKeys.concat(checked.halfCheckedKeys);
      emit("update:modelValue", checked.checkedKeys);
    };

    watch(
      () => props.modelValue,
      (newVal, OldVal) => {
        if (!isEqual(newVal, OldVal)) {
          instance.refs.tree.setCheckedKeys(newVal);
        }
      }
    );
  },
  template: /*html*/ `
    <el-tree ref="tree" v-bind="item"></el-tree>
    `,
};

const MfDatePicker = {
  props: { ...commProps },
  emits: ["update:modelValue"],
  types: [
    "year",
    "month",
    "week",
    "dates",
    "date",
    "datetime",
    "monthrange",
    "daterange",
    "datetimerange",
  ],
  setup: (props, ctx) => {
    const formValue = ref(props.modelValue);
    const updateModelValue = (value) => {
      emit("update:modelValue", value);
    };
    watch(
      () => props.modelValue,
      (val) => (formValue.value = val)
    );

    //  设置默认属性
    if (["dates", "date", "daterange"].includes(props.item.type)) {
      props.item["value-format"] = props.item["value-format"] || "YYYY-MM-DD";
    } else if (["datetime", "datetimerange"].includes(props.item.type)) {
      props.item["value-format"] =
        props.item["value-format"] || "YYYY-MM-DD HH:mm:ss";
    } else if (["month", "monthrange"].includes(props.item.type)) {
      props.item["value-format"] = props.item["value-format"] || "YYYY-MM";
    }
    if (props.item.type.endsWith("range")) {
      props.item["range-separator"] = props.item["range-separator"] || "至";
      props.item["start-placeholder"] =
        props.item["start-placeholder"] || "开始日期";
      props.item["end-placeholder"] =
        props.item["end-placeholder"] || "结束日期";
      props.item["align"] = props.item["align"] || "right";
    }

    return {
      formValue,
      updateModelValue,
    };
  },
  includes: (tag) => {
    return MfDatePicker.types.includes(tag);
  },
  template: /*html*/ `
    <el-date-picker v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)"
        v-bind="item">
    </el-date-picker>
    `,
};

const MfTimePicker = {
  ...commOptions,
  types: ["time", "timerange"],
  includes: (tag) => {
    return MfTimePicker.types.includes(tag);
  },
  template: /*html*/ `
    <el-time-picker v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)"
        v-bind="item">
    </el-time-picker>
    `,
};

const MfTimeSelect = {
  ...commOptions,
  template: /*html*/ `
    <el-time-select v-model="formValue" @update:modelValue="$emit('update:modelValue', $event)"
        v-bind="item">
    </el-time-select>
    `,
};

export {
  MfComponentHub,
  MfKeyValue,
  MfXselect,
  MfIconSelector,
  MfInputDict,
  MfSelectSearch,
  MfUpload,
  MfEditor,
  MfInput,
  MfUploadRaw,
  MfSwitch,
  MfSelect,
  MfSelectMultiple,
  MfRadio,
  MfCheckbox,
  MfTree,
  MfDatePicker,
  MfTimePicker,
  MfTimeSelect,
  MfLabel,
  MfImage,
  MfHtml,
  MfPage,
  MfCombEditor,
};
