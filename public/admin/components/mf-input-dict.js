export default {
  data() {
    return {};
  },
  props: {
    modelValue: {
      type: Object,
      default: () => {},
    },
    options: {
      type: Array,
      default: () => [],
    },
  },
  emits: ["update:modelValue"],
  computed: {
    values: {
      get: function () {
        return this.modelValue;
      },
      set: function (newValue) {
        this.$emit("update:modelValue", newValue);
      },
    },
  },
  template: /*html*/ `
<div class="input-list" style="width: 100%;">
    <div v-for="(option, index) in options" :key="index">
        <el-input v-bind="option" style="margin-bottom: 5px;" v-model="values[option.key]">
            <template v-if="option.prepend" #prepend>{{option.prepend}}</template>
            <template v-if="option.append" #append>{{option.append}}</template>
        </el-input>
    </div>
</div>
`,
};
