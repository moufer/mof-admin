import { computed, reactive, ref } from "vue";
import { camelCase, upperFirst, isNil } from "lodash-es";
import * as MfComponents from "/src/components/mf-components.js";

export default {
  components: MfComponents,
  emits: ["update:modelValue"],
  props: {
    tabData: {
      type: Object,
    },
    item: {
      type: Object,
      required: true,
    },
    modelValue: {
      type: [Object, Array, String, Number, Boolean],
      default: () => {
        return {};
      },
    },
    showHelper: {
      type: Boolean,
      default: true,
    },
    scene: "", //场景，用于区分不同的表单
  },
  setup(props, { emit }) {
    const components = ref({});
    const component = ref("");
    const helper = ref("");
    const formItemAttrs = reactive({});

    if (!props.item.type) props.item.type = "input";
    let type = props.item.type;
    if (MfComponents.MfComponentHub.isCommonlyTag(type)) {
      type = "component_hub";
    } else if (MfComponents.MfDatePicker.includes(type)) {
      type = "date_picker";
    } else if (MfComponents.MfTimePicker.includes(type)) {
      type = "time_picker";
    } else if (["input", "password", "textarea"].includes(type)) {
      type = "input";
    }
    //组件名
    component.value = upperFirst(camelCase("mf_" + type));

    //属性
    formItemAttrs.label = props.item.label;
    formItemAttrs.prop = props.item.prop;
    formItemAttrs.tip = props.item.tip;

    //根据场景找帮助信息
    let helperName = "intro";
    if (props.scene) {
      const sceneHelperName = camelCase("intro_" + props.scene);
      if (props.scene && !isNil(props.item[sceneHelperName])) {
        helperName = sceneHelperName;
      }
    }
    if (!isNil(props.item[helperName])) {
      helper.value = props.item[helperName];
    }

    const computedItemValue = computed({
      get: () => props.modelValue,
      set: (newValue) => {
        emit("update:modelValue", newValue);
      },
    });

    return {
      formItemAttrs,
      helper,
      component,
      computedItemValue,
    };
  },
  template: /*html*/ `
    <el-form-item :class="{'form-item-sub':formItemAttrs.label.length===0}" :prop="formItemAttrs.prop">
        <template #label>
            <div style="display:flex;align-items:center;">
                <span style="margin-right:3px">{{formItemAttrs.label}}</span>
                <el-tooltip v-if="formItemAttrs.tip"
                    :show-after="100"
                    :content="formItemAttrs.tip"
                    raw-content
                ><el-icon><InfoFilled /></el-icon></el-tooltip>
            </div>
        </template>
        <template #default>
            <component v-if="component.length > 0" :is="component" :item="item" v-model="computedItemValue" />
            <div class="form-item-helper" v-if="helper.length > 0" v-show="showHelper" v-html="helper"></div>
        </template>
    </el-form-item>
    `,
};
