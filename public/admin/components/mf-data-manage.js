import MfDataTransmit from './mf-data-transmit.js';
import MfDataSearch from './mf-data-search.js';
import MfDataToolbar from './mf-data-toolbar.js';
import MfDataTable from './mf-data-table.js';
import MfDataFormDialog from './mf-data-form-dialog.js';
import { ElMessage } from 'element-plus';
import { isEmpty, evaluateExpression } from 'comm/utils.js';

export default {
    components: {
        MfDataSearch, MfDataToolbar, MfDataTable, MfDataTransmit, MfDataFormDialog
    },
    inject: ['http'],
    props: {
        tableConfigUrl: {
            type: String,
            default: ''
        },
        tableName: {
            type: String,
            default: ''
        },
        tableColumnsInfo: {
            type: Array,
            default: []
        },
        tableColumnOperations: {
            type: Object,
            default: {}
        },
        tableColumnFormatter: {
            type: Object,
            default: {}
        }
    },
    emits: ['table-selection-change', 'table-operation-click', 'toolbar-click', 'table-data-change'],
    data() {
        return {
            serverBaseUrl: '',//服务器地址
            serverActions: {},//服务器接口

            tabs: [],       //Tabs组件
            tabProp: '',
            activeTab: '',

            tableColumns: [],//表格列信息
            columns: [],//表格列信息
            tableOperations: [],//表格列操作
            tableSelection: true,//表格多选

            pk: 'id',//表主键名称
            sortField: '_PK_',//排序字段
            sort: 'desc',//排序方式

            data: [],//数据表格数据
            loading: false,//数据表格加载状态
            selectionCount: 0,//选中的数据条数

            total: 0,//数据总条数
            pageNum: 1,//当前页码
            pageSize: 10,//每页显示条数
            showPagination: true,//是否显示分页

            query: {},//查询条件
            searchItems: [],//搜索选项
            showSearch: true,//是否显示搜索条
            searchItemsValue: [], //查询项目

            toolbarButtons: [],//工具栏按钮

            formItems: [],//表单项目
            formItemsValue: [],//表单项目

            detailDialogVisible: false, //详情对话框显示
            detailData: null, //详情内容
        }
    },
    created() {
        //从服务器加载表格配置
        this.getTableConfig().then(res => {
            const data = res.data || {};
            //优先采用本地配置的属性组
            let propMappings = ['tableColumnsInfo:tableColumns', 'tableColumnOperations:tableOperations'];
            //遍历data,将data中的每个对象赋值到this.data
            for (const [key, value] of Object.entries(data)) {
                //如果有props里的配置，则有限使用props配置
                let lastIndex = propMappings.findIndex(item => item.endsWith(`:${key}`));
                let propValue = null;
                if (lastIndex > -1) {
                    let propKey = propMappings[lastIndex].replace(`:${key}`, '');
                    //判断this[propKey]是否为空或undefined
                    if (!isEmpty(this[propKey])) {
                        propValue = this[propKey];
                        this[key] = propValue;
                    }
                    propMappings.splice(lastIndex, 1);
                }
                //如果没有配置props里的配置，则直接赋值
                //console.log(key, value)
                if (!propValue) this[key] = value;
            }
            this.initConfig();
            this.$nextTick(() => {
                this.search();
            });
        }).catch(err => {
            err.errmsg && ElMessage.error(err.errmsg);
        });
    },
    methods: {
        //获取元素
        getRef(elName) {
            return this.$refs[elName];
        },

        //获取表格配置
        getTableConfig() {
            let url = this.tableConfigUrl || '/system/table/{tableName}';
            return this.http.get(url.replace('{tableName}', this.tableName));
        },

        //重新加载表格字段
        reloadTableColumns() {
            this.getTableConfig().then(data => {
                this.tableColumns = data.tableColumns;
                this.initFormItems();
                this.initTableColumns();
            }).catch(err => {
                console.log(err);
                err.errmsg && ElMessage.error(err.errmsg);
            });
        },

        //获取数据
        load() {
            let query = {};
            //过滤掉this.query里值为null和undefined的属性
            for (let key in this.query) {
                if (this.query[key] !== null && typeof this.query[key] !== 'undefined') {
                    query[key] = this.query[key];
                }
            }
            //排序
            let order = {
                field: this.sortField === '_PK_' ? this.pk : this.sortField,
                sort: this.sort
            }
            //分页
            let page = {
                pageNum: this.pageNum,
                pageSize: this.pageSize
            }

            //根据query变化tableColumns的显示情况
            this.visibleTableColumns(query);

            this.loading = true;
            this.$refs.transmit.search(query, order, page).then(res => {
                this.data = res.data.data || res.data || []; //数据
                //this.pageNum = res.data.current_page || 1; //当前页码
                //this.pageSize = res.data.per_page; //每页显示条数
                this.total = res.data.total || this.data.length; //数据总条数
            }).catch(err => {
                err.errmsg && ElMessage.error(err.errmsg);
            }).finally(() => {
                this.loading = false;
            });
        },

        //刷新
        refresh() {
            this.load();
        },

        //搜索
        search() {
            //console.log('search', this.query);
            this.loading = true;
            this.pageNum = 1;
            this.load();
        },

        //新增对话框
        add(params = null) {
            this.$refs.transmit.add(params).then(res => {
                this.$refs.dialog.addDialog(res.data.dialog, res.data.form, res.data.elements);
            }).catch(err => {
                err.errmsg && ElMessage.error(err.errmsg);
            });
        },

        //编辑对话框
        edit(id, params = null) {
            //提示加载中
            //let loading = ElementPlus.ElLoading.service({ text: '加载中' });
            this.$refs.transmit.edit(id, params).then(res => {
                // const data = res.data;
                // const itemKeys = this.formItemsValue.map(item => item.prop);
                // //找出交集
                // const commonKeys = Object.keys(data).filter(key => itemKeys.includes(key));
                // //把共同的key的值赋值给values
                // const values = {};
                // commonKeys.forEach(key => values[key] = data[key]);
                this.$refs.dialog.editDialog(id, res.data.dialog, res.data.form, res.data.elements);
            }).catch(err => {
                err.errmsg && ElMessage.error(err.errmsg);
            }).finally(() => {
                //关闭加载中提示
                //loading.close();
            });
        },

        //删除
        delete(ids) {
            this.$refs.transmit.delete(ids).then(res => {
                //数据变化事件
                this.$emit('table-data-change', 'delete', { ids });
                ElMessage.success('操作成功');
                this.load();
            }).catch(err => {
                err.errmsg && ElMessage.error(err.errmsg);
            });
        },

        //批量操作
        batch(ids, field, value) {
            this.$refs.transmit.batch(ids, field, value).then(res => {
                //数据变化事件
                this.$emit('table-data-change', 'batch', { ids, field, value });
                ElMessage.success('操作成功');
                this.load();
            }).catch(err => {
                err.errmsg && ElMessage.error(err.errmsg);
            });
        },

        //详情
        detail(id, row)
        {
            //提示加载中
            //let loading = ElementPlus.ElLoading.service({ text: '加载中' });
            this.$refs.transmit.read(id).then(res => {
                let data = [];
                Object.keys(res.data).forEach(key => {
                    const column = this.tableColumns.filter(item => key === item.prop || key === item.propAlias)
                    data.push({
                        column: key, label: column[0]?.label, value: res.data[key]
                    });
                });
                this.detailData = data;
                this.detailDialogVisible = true;
            }).catch(err => {
                err.errmsg && ElMessage.error(err.errmsg);
            }).finally(() => {
                //关闭加载中提示
                //loading.close();
            });
        },

        //提交表单
        submit(action, data, pkId) {
            if ('edit' === action) {
                data[this.pk] = pkId;
            } else if (typeof data[this.pk] !== 'undefined') {
                delete data[this.pk];
            }
            this.$refs.transmit.save(data).then(res => {
                this.$refs.dialog.hideDialog();
                //数据变化事件
                this.$emit('table-data-change', action, data);
                ElMessage.success('提交成功');
                this.load();
            }).catch(err => {
                err.errmsg && ElMessage.error(err.errmsg);
            });
        },

        //初始化表格配置
        initConfig() {
            this.initTableOperations(); //表格操作
            this.initTabs(); //tabs
            this.initTableColumns(); //表格列信息
            this.initSearchQuery(); //筛选
        },

        //初始化列操作按钮配置
        initTableOperations() {
            for (let index = 0; index < this.tableOperations.buttons.length; index++) {
                const button = this.tableOperations.buttons[index];
                //如果按钮时字符串
                if (typeof button === 'string') {
                    let btnInfo = button.split('|');
                    let attrs = {};
                    //button是一个表达式，如:edit|label:编辑|icon:Edit|name:edit
                    btnInfo.length > 0 && btnInfo.forEach(btnArr => {
                        let arr = btnArr.split(':');
                        attrs[arr[0]] = arr[1];
                    });
                    switch (btnInfo[0]) {
                        case 'detail':
                            this.tableOperations.buttons[index] = {
                                label: '详情', icon: 'Tickets', name: 'detail',
                            };
                            break;
                        case 'edit':
                            this.tableOperations.buttons[index] = {
                                label: '编辑', icon: 'Edit', name: 'edit',
                            };
                            break;
                        case 'delete':
                            this.tableOperations.buttons[index] = {
                                label: '删除', icon: 'Delete', name: 'delete', confirm: { title: '确定要删除吗?' }
                            }
                            break;
                    }
                    //合并表达式数据
                    if (!isEmpty(attrs) && typeof this.tableOperations.buttons[index] === 'object') {
                        Object.assign(this.tableOperations.buttons[index], attrs);
                    }
                }
            }
        },

        //tabs初始化
        initTabs() {
            //如果有表格列信息，则根据表格列信息生成表单项目
            // if (this.tableColumns.length > 0) {
            //     this.tableColumns.forEach(element => {
            //         if (element.type === 'select' && typeof element.tab === 'string') {
            //             this.activeTab = element.tab;
            //             this.tabProp = element.prop;
            //             this.tabs = element.options;
            //         }
            //     });
            // }
            //console.log('this.tabs', this.tabs);
        },

        //表格列信息初始化
        initTableColumns() {
            let result = [];
            //如果有表格列信息，则根据表格列信息生成表单项目
            if (this.tableColumns.length > 0) {
                result = this.tableColumns.filter(item => item.visible !== false).map(item => {
                    return item;
                });
                //前端自定义了字段内容格式化，需要写入到表格列信息中
                const formatterKeys = Object.keys(this.tableColumnFormatter);
                if(formatterKeys.length > 0) {
                    formatterKeys.forEach(key => {
                        result.forEach(item => {
                            const prop = item.propAlias || item.prop
                            if(prop === key) item.formatter = this.tableColumnFormatter[key];
                        });
                    });
                }
            }
            this.columns = result;
        },

        //查询条件初始化
        initSearchQuery() {
            let result = {};
            //如果有表格列信息，则根据表格列信息生成表单项目
            // if (this.tableColumns.length > 0) {
            //     this.tableColumns.forEach(item => {
            //         const prop = item.propAlias || item.prop;
            //         if (item.search) {
            //             if (['switch'].indexOf(item.type) > -1) {
            //                 result[prop] = item.defaultValue || false;
            //             } else {
            //                 result[prop] = item.defaultValue || null;
            //             }
            //         }
            //     });
            // }
            //默认tab
            if (this.activeTab) {
                result[this.tabProp] = this.activeTab;
            }
            //默认筛选
            if (this.defaultQuery) {
                result = Object.assign(result, this.defaultQuery);
            }
            this.query = result;
        },

        //根据查询条件来显示表格列
        visibleTableColumns(query) {
            this.tableColumns.forEach(column => {
                //根据表达式来
                column.visible = column.visibleExpr ? evaluateExpression(column.visibleExpr, query) : true;
            });
        },

        //点击tab事件
        handleTabClick(tab) {
            this.$refs.search.changeQuery(this.tabProp, tab.props.name).search();
        },

        //选择操作
        handelSelectionChange(selection) {
            this.selectionCount = selection.length;
            this.$emit('table-selection-change', selection);
            //console.log('selectionCount', this.selectionCount);
        },

        //分页事件
        handlePageChange(num) {
            this.pageNum = num.pageNum; //设置当前页码
            this.pageSize = num.pageSize; //设置每页显示条数
            this.load();
        },

        //点击工具栏按钮
        handleToolbarClick(name, data) {
            console.log('handleToolbarClick', name, data);
            let ids = [];
            let rows = this.$refs.table.getSelection();
            if (rows.length > 0) {
                ids = rows.map(item => item[this.pk]);
            }
            if ((!ids || ids.length == 0) && ['edit', 'delete', 'status'].indexOf(name) > -1) {
                ElMessage.warning('请选择一条数据');
                return;
            }
            switch (name) {
                case 'refresh':
                    this.load();
                    break;
                case 'add':
                    this.add();
                    break;
                case 'edit':
                    this.edit(ids[0]);
                    break;
                case 'delete':
                    this.delete(ids);
                    break;
                case 'status':
                    this.batch(ids, 'status', data.value);
                    break;
                case 'updates':
                    this.batch(ids, data.field, data.value);
                    break;
                case 'search':
                    this.showSearch = !this.showSearch;
                    break;
                default:
                    this.$emit('toolbar-click', name, data);
            }
        },

        //点击表格行操作按钮
        handleOperationClick(name, row, index, button) {
            //如果button.click事件存在，则执行button.click事件
            if (typeof button.click === 'function') {
                button.click(row, index, this);
            } else {
                switch (name) {
                    case 'detail':
                        this.detail(row[this.pk], row);
                        break;
                    case 'edit':
                        this.edit(row[this.pk]);
                        break;
                    case 'delete':
                        this.delete([row[this.pk]]);
                        break;
                    default:
                        //再向上层传递事件
                        this.$emit('table-operation-click', name, row, index, button, this);
                }
            }
        }
    },
    template: /*html*/`
    <div class="data-manage" v-if="tableColumns.length > 0">
        <el-tabs v-model="activeTab" type="card" @tab-click="handleTabClick" v-if="tabs.length > 0">
            <el-tab-pane v-for="tab in tabs" :label="tab.label" :name="tab.name"></el-tab-pane>
        </el-tabs>
        <div class="table-box" :style="{borderTopWidth:tabs.length>0?'0':'1px'}">
            <MfDataTransmit ref="transmit" :base-url="serverBaseUrl" :actions="serverActions"
                :data-pk="pk" :query="query"></MfDataTransmit>
            <MfDataSearch ref="search" v-model="query" v-show="showSearch" @search="search"
                :columns="tableColumns" :items="searchItems"></MfDataSearch>
            <MfDataToolbar ref="toolbar" :buttons="toolbarButtons" :selection-count="selectionCount"
                @click="handleToolbarClick">
                <template #left="scope">
                    <slot name="toolbar-left" :selection-count="scope.selectionCount"></slot>
                </template>
                <template #right="scope">
                    <slot name="toolbar-right" :selection-count="scope.selectionCount"></slot>
                </template>
            </MfDataToolbar>
            <div class="table-body">
                <MfDataTable ref="table" :data="data" :loading="loading" :selection="tableSelection"
                    :operation="tableOperations" :columns="columns" :total="total" :pageSizes="pageSizes"
                    v-model:page-num="pageNum" v-model:page-size="pageSize" :show-pagination="showPagination"
                    @selection-change="handelSelectionChange" @operation-click="handleOperationClick"
                    @pagination-change="handlePageChange"
                >
                    <template #columns="scope">
                        <slot name="table-columns" :row="scope.row"></slot>
                    </template>
                    <template #operate="scope">
                        <slot name="table-operate" :row="scope.row"></slot>
                    </template>
                </MfDataTable>
            </div>
        </div>
    </div>
    <slot name="form-dialog">
        <MfDataFormDialog ref="dialog" @submit="submit" />
    </slot>
    <slot name="detail-dialog">
        <el-dialog v-model="detailDialogVisible" title="详情" width="800">
            <el-descriptions border :column="1">
                <el-descriptions-item v-for="item in detailData"
                    :label="item.label" label-align="right">
                    <template #label>
                        <label v-if="item.label">{{item.label}}</label>
                        {{item.column}}
                    </template>
                    {{item.value}}
                </el-descriptions-item>
            </el-descriptions>
        </el-dialog>
    </slot>
    `
}