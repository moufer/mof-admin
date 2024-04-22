//非构建方式写一个vue3组件
export default {
    inject: ['http'],
    data() {
        return {
            item: []
        }
    },
    props: {
        modelValue: {
            type: Array,
            default: []
        }
    },
    emits: ['update:modelValue'],
    onMounted() {
        this.getOptions(this.item);
    },
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
        // 获取下拉选项
        getOptions(options) {
            this.http.get(options.url, options.params || {}).then(res => {
                if (res.data.errcode === 0) {
                    options.options = res.data.data.data.map(item => {
                        return {
                            label: item['name'],
                            value: item['id']
                        };
                    });
                }
            });
        }
    },
    template: /*html*/`
    <el-select v-model="formValue" placeholder="请选择" :clearable="item.clearable">
        <el-option v-for="option in item.options" :key="option.value" :label="option.label" 
            :value="option.value"></el-option>
    </el-select>
  `
}