import { ref } from "vue";

export default {
  props: {},
  emits: [],
  setup(props, { emit }) {
    const visible = ref(false);
    const title = "123";
    const onBeforeClose = () => {
      console.log("onBeforeClose");
    };

    return {
      visible,
      onBeforeClose,
    };
  },
  template: /*html*/ `
  <el-dialog v-model="visible"  :lock-scroll="true" :before-close="onBeforeClose">
        <template #default>
        </template>
        <span slot="title">{{title}}</span>
        <div slot="footer" class="dialog-footer">
            <el-button @click="visible = false">取 消</el-button>
            <el-button type="primary" @click="onSubmit">确 定</el-button>
        </div>
    </el-dialog>
  `,
};
