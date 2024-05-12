import MfFormRender from './mf-form-render.js';
export default {
    name: 'mf-data-search',
    components: { MfFormRender },
    props: {
        modelValue: {},
        columns: {
            type: Array,
            default: () => []
        },
        items: {
            type: Array,
            default: () => []
        }
    },
    emits: ['update:modelValue', 'search'],
    computed: {
        query: {
            get: function () {
                return this.modelValue
            },
            set: function (newValue) {
                this.$emit('update:modelValue', newValue)
            }
        },
        searchItems() {
            if (this.items.length == 0) {
                let result = [];
                let attrs = {
                    "select": ['options'],
                    "radio": ['options'],
                }
                if (this.columns.length > 0) {
                    result = this.columns.filter(item => item.search).map(item => {
                        let search = item.search;
                        (attrs[search.type] || []).forEach(attr => {
                            if (typeof item[attr] !== 'undefined') {
                                search[attr] = item[attr];
                            }
                        });
                        if (['select', 'input'].indexOf(search.type) > -1) {
                            if (typeof search.clearable === 'undefined') {
                                search.clearable = true;
                            }
                        }
                        search.label = item.label;
                        if (typeof search.prop === 'undefined') {
                            search.prop = item.propAlias || item.prop;
                        }
                        if (typeof this.query[search.prop] === 'undefined') {
                            this.query[search.prop] = null;
                        }
                        return search;
                    });
                }
                return result;
            }
            return this.items;
        },
    },
    methods: {
        //通过表达式，判断表单项目是否显示
        showItem(expStr) {
            return expStr ? evaluateExpression(expStr, this.model) : true;
        },

        changeQuery(prop, value) {
            this.query[prop] = value;
            return this;
        },

        search() {
            //触发搜索事件
            this.$emit('search', this.query);
        }
    },
    template: /*html*/`
    <div class="table-search">
        <el-form :inline="false" :model="query">
            <el-row :gutter="10">
                <template v-for="item in searchItems" :key="item.prop">
                <el-col :span="item.colSpan||6">
                    <mf-form-render :item="item" v-model="query[item.prop]" />
                </el-col>
                </template>
                <el-col :span="4">
                    <el-form-item>
                        <el-button type="primary" @click="search">搜索</el-button>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
    </div>
    `,
}