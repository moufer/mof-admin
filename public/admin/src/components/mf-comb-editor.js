export default {
  name: "mf-comb-editor",
  props: {
    button: {
      type: Object,
      default: {},
    },
  },
  data() {
    return {
      showInput: false,
      inputValue: "",
    };
  },
  methods: {},

  template: /* HTML */ `
    <div>
      <el-button v-if="button.show" v-bind="button"
        >{{ button.label }}</el-button
      >
    </div>
  `,
};
