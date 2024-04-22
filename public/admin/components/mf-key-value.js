//非构建方式写一个vue3组件
export default {
  data() {
    return {
      items: []
    }
  },
  props: {
    modelValue: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:modelValue'],
  computed: {
    value: {
      get: function () {
        return this.modelValue
      },
      set: function (newValue) {
        this.$emit('update:modelValue', newValue)
      }
    }
  },
  methods: {
    append: function () {
      this.value.push({ key: '', value: '' })
    },
    remove: function (index) {
      this.value.splice(index, 1)
    }
  },
  template: /*html*/`
<div class="keyValue">
  <el-row :gutter="5">
    <el-col :span="8">键(Key)</el-col>
    <el-col :span="10">值(Value)</el-col>
  </el-row>
  <el-row v-for="(item, index) in value" :gutter="5">
    <el-col :span="8">
    <el-input v-model="item.key" />
    </el-col>
    <el-col :span="10">
    <el-input v-model="item.value" />
    </el-col>
    <el-col :span="6">
    <el-button type="danger" @click="remove(index)">x</el-button>
    </el-col>
  </el-row>
  <el-row>
    <el-col :span="24">
      <el-button type="primary" @click="append">追加</el-button>
    </el-col>
  </el-row>
</div>
`
}