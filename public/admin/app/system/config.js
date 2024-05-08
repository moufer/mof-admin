import { ref, reactive, inject, onMounted, onUnmounted, getCurrentInstance } from 'vue';
import { ElMessage } from 'element-plus';
import MfFormRender from '../../components/mf-form-render.js';
import { evaluateExpression, addStyle } from 'comm/utils.js';

export default {
    name: 'mf-page-config',
    components: { MfFormRender },
    props: {
        submitUrl: {
            type: String,
            default: '/system/config/system'
        },
        module: {
            type: String,
            default: 'system'
        },
        tabOptions: {
            type: Object,
            default: {}
        }
    },
    setup(props, { emit }) {
        const instance = getCurrentInstance();
        const http = inject('http');
        const module = props.module || 'system';
        const submitUrl = props.submitUrl || `/system/config/${module}`;

        const tabs = ref([]);
        const formData = reactive({});
        const activeTab = ref('base');
        const loading = ref(false);
        const position = ref('right');

        //组件样式动态添加
        const styleStr = instance.type?.style;
        const styleEl = styleStr ? addStyle(styleStr) : null;

        onUnmounted(() => {
            //移除样式
            styleEl?.remove();
        });

        onMounted(() => {
            if (window.innerWidth < 768) {
                position.value = 'top';
            }
            window.addEventListener('resize', () => {
                if (window.innerWidth < 768 && position.value !== 'top') {
                    position.value = 'top';
                } else if (window.innerWidth >= 768 && position.value !== 'right') {
                    position.value = 'right';
                }
            });
        });

        const showItem = (expStr, tabProp) => {
            return expStr ? evaluateExpression(expStr, formData[tabProp]) : true;
        }

        http.get(submitUrl).then(res => {
            tabs.value = res.data;
            tabs.value.forEach(tab => {
                tab.options && tab.options.forEach(option => {
                    if (typeof formData[tab.prop] === 'undefined') {
                        formData[tab.prop] = {};
                    }
                    formData[tab.prop][option.prop] = option.value;
                    delete option.value;
                });
            });
        });
        //提交表单
        const submitForm = (e) => {
            let postData = {};
            tabs.value.forEach(tab => {
                if (tab.prop === activeTab.value) {
                    tab.options && tab.options.forEach(option => {
                        postData[option.prop] = formData[tab.prop][option.prop];
                        if (['uploadfile', 'uploadimage'].includes(option.type) &&
                            formData[tab.prop][option.prop].length === 0) {
                            postData[option.prop] = [];
                        }
                    });
                }
            });
            //提交到后端保存配置
            loading.value = true;
            //暂停间歇
            setTimeout(() => {
                http.post(submitUrl, { rows: postData })
                    .then(() => ElMessage({ message: '提交成功', type: 'success', duration: 1000 }))
                    .catch(err => ElMessage({ message: err.errmsg, type: 'error', duration: 1000 }))
                    .finally(() => loading.value = false);
            }, 200);
        };
        const tabClick = (tab) => {
            //console.log('tabClick', tab)
        }

        return {
            tabs, formData, activeTab, loading, position,
            submitForm, tabClick, showItem
        }
    },
    template: /*html*/`
    <div class="page-config">
        <el-tabs v-model="activeTab" @tab-click="tabClick" v-bind="tabOptions">
        <el-tab-pane v-for="tab in tabs" :label="tab.label" :name="tab.prop" :lazy="true" style="padding-top:10px;">
            <el-form label-width="200px" :label-position="position" :model="formData[tab.prop]">
            <el-row>
                <el-col :xs="24" :sm="22" :md="20" :lg="19" :xl="17">
                <template v-for="item in tab.options">
                    <el-form-item v-if="item.type === 'divider'" v-show="showItem(item._visible, tab.prop)">
                        <el-divider v-bind="item">{{item.label}}</el-divider>
                    </el-form-item>
                    <mf-form-render v-else v-show="showItem(item._visible, tab.prop)"
                        :item="item" v-model="formData[tab.prop][item.prop]"></mf-form-render>
                </template>
                <el-form-item v-if="tab.options.length > 0">
                    <el-button type="primary" size="large" @click="submitForm" :loading="loading">提交</el-button>
                </el-form-item>
                </el-col>
            </el-row>
            </el-form>
        </el-tab-pane>
        </el-tabs>
    </div>
    `,
    'style': /*css*/`
    .page-config .el-divider--horizontal{
        margin: 10px 0;
    }
    `
}