//非构建方式写一个vue3组件
export default {
    props: {
        modelValue: {
            type: [String, Number],
            default: () => ''
        },
        source: {
            type: String,
            required: true
        },
        keyField: {
            type: String,
            default: 'id',
        },
        keyValue: {
            type: [String, Number],
            default: '',
        },
        searchField: {
            type: String,
            default: 'name',
        },
        labelField: {
            type: String,
            default: 'name',
        },
        orderBy: {
            type: Object,
            default: { field: 'name', sort: 'asc' }
        },
        custom: {
            type: Object,
            default: {}
        }
    },
    data() {
        return {
            options: [],
            loading: false,
            keyword: '',
            defaultValue: ''
        }
    },
    emits: ['update:modelValue'],
    inject: ['http'],
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
    mounted() {
        this.defaultValue = this.keyValue;
        this.remoteMethod('', true);
    },
    methods: {
        remoteMethod(query = '', first = false) {
            let url = this.source;
            let params = {
                keyField: this.keyField,
                keyValue: first ? this.keyValue : '',
                searchField: this.searchField,
                searchValue: query,
                orderBy: this.orderBy,
                custom: this.custom
            }
            //params转换为url的参数
            url += '?' + Object.keys(params).map(key => {
                //params[key]可能是对象，需要拆开，遍历
                if (typeof params[key] === 'object') {
                    return Object.keys(params[key]).map(_key => {
                        return key + '[' + _key + ']=' + encodeURIComponent(params[key][_key])
                    }).join('&');
                }
                return key + '=' + params[key]
            }).join('&');
            this.loading = true;
            this.http.get(url).then(res => {
                this.loading = false;
                this.options = res.data.data.map(item => {
                    return {
                        label: item[this.labelField], value: item[this.keyField]
                    }
                });
            });
        }
    },
    template: /*html*/`
    <el-select
        v-model="value"
        filterable
        remote
        remote-show-suffix
        clearable
        placeholder="请输入搜索关键字"
        :remote-method="remoteMethod"
        :loading="loading"
    >
        <el-option
        v-for="item in options"
        :key="item.value"
        :label="item.label"
        :value="item.value"
        />
    </el-select>
`
}