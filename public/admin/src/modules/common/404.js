import { computed, ref } from "vue";
import { useRoute } from "vue-router";

export default {
  setup() {
    const route = useRoute();
    const path = computed(() => route.path);
    return { path };
  },
  template: /*html*/ `
  <el-result
        icon="error"
        title="页面不存在"
        sub-title="您访问的页面不存在，请检查地址是否正确。"
      >
        <template #extra>
          <el-button type="primary" @click="$router.go(-1)">返回上一页</el-button>
        </template>
      </el-result>
  `,
};
