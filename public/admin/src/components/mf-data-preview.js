import { ref } from "vue";

export default {
  props: {
    src: {
      type: [String, Array],
      required: true,
    },
    type: {
      type: String,
      required: true,
    },
    cover: {
      type: String,
    },
  },
  setup(props) {
    const src = ref(props.src);
    const type = ref(props.type);
    const dialogVisible = ref(false);

    return {
      src,
      type,
      dialogVisible,
    };
  },
  template: /*html*/ `
    <div>
      <slot name="default">
        <div v-if="type === 'images'">
          <el-image v-for="(item, index) in src" :src="item" :preview-src-list="src" :initial-index="index"
            preview-teleported style="max-width:80px;max-height:80px;margin-right:2px;" />
        </div>
        <div v-else-if="type === 'image'">
          <el-image :src="src" :preview-src-list="[src]" 
            preview-teleported style="max-width:80px;max-height:80px;" />
        </div>
        <div v-else>
          <el-image :src="cover" @click="dialogVisible = true"
            style="max-width:80px;max-height:80px;cursor:pointer;" />
        </div>
      </slot>
    </div>
    <el-dialog v-model="dialogVisible" :lock-scroll="false" title="预览" width="600" append-to-body>
        <div style="height: 100%;display: flex;align-items: center;justify-content: center;">
          <audio :src="src" controls v-if="type === 'audio'" style="width:100%;"></audio>
          <video :src="src" controls v-else-if="type === 'video'" style="max-height:600px;"></video>
          <div v-else style="text-align:center;">
              <p>不支持预览的web资源类型</p>
          </div>
        </div>
    </el-dialog>
  `,
};
