import MfFormItems from './mf-form-items.js';


const DataSearch = {
    name: 'data-search',
    components: {
        MfFormItems
    },
    props: {
        modelValue: {},
        items: []
    },
    data() {
        return {
        }
    },
    emits: ['update:modelValue', 'search', 'reset'],
    computed: {
        query: {
            get: function () {
                return this.modelValue
            },
            set: function (newValue) {
                this.$emit('update:modelValue', newValue)
            }
        }
    },
    created() {
    },
    methods: {
        search() {
            this.$emit('search', this.query)
        },
        reset() {
            this.$emit('reset')
        },
        toggle() {
            this.show = !this.show
        }
    },
    template: `
    <div class="table-search">
        <el-form :inline="true" :model="query">
            <mf-form-items v-model:values="query" :items="items"></mf-form-items>
            <el-form-item>
                <el-button type="primary" @click="search">搜索</el-button>
            </el-form-item>
        </el-form>
    </div>
    `,
}

const DataToolbar = {
    emits: ['click'],
    props: {
        tableHandle: null,
        operationHandle: null,
        dialogHandle: null,
        count: {
            type: Number,
            default: 0
        },
        buttons: {
            type: Array,
            default: () => ['refresh', 'add', 'edit', 'delete', 'status', 'search']
        },
        statusTypes: {
            type: Array,
            default: () => [
                { label: '启用', value: 1, icon: 'View' },
                { label: '禁用', value: 0, icon: 'Hide' }
            ]
        },
        appendButtons: {}
    },
    data() {
        return {
        }
    },
    methods: {
        click(event, button) {
            if ('delete' === button) {
                this.delete()
            } else {
                this.$emit('click', button)
            }
        },
        delete() {
            this.$confirm(`此操作将永久删除 ${this.count} 条数据, 是否继续?`, {
                type: 'warning'
            }).then(() => {
                this.$emit('click', 'delete')
            }).catch(() => { });
        },
        handleStatusCommand(command) {
            this.$emit('click', 'status', { value: command })
        }
    },
    template: /*html*/`
    <div class="table-toolbar">
    <div class="left">
      <el-button type="success" @click="click($event,'refresh')" v-if="buttons.indexOf('refresh')>-1">
        <el-icon>
          <component is="Refresh"></component>
        </el-icon>
      </el-button>
      <el-button type="primary" @click="click($event,'add')" v-if="buttons.indexOf('add')>-1">
        <el-icon>
          <component is="Plus"></component>
        </el-icon>
      </el-button>
      <el-button type="warning" @click="click($event,'edit')" :disabled="count==0"
        v-if="buttons.indexOf('edit')>-1">
        <el-icon>
          <component is="Edit"></component>
        </el-icon>
      </el-button>
      <el-button type="danger" @click="click($event,'delete')" :disabled="count==0"
        v-if="buttons.indexOf('delete')>-1">
        <el-icon>
          <component is="Delete"></component>
        </el-icon>
      </el-button>
      <el-dropdown @command="handleStatusCommand">
        <el-button type="warning" :disabled="count==0"
            v-if="buttons.indexOf('status')>-1">
            <el-icon>
                <component is="Open"></component>
            </el-icon>
            </el-button>
            <template #dropdown>
                <el-dropdown-menu>
                    <el-dropdown-item :command="status.value" v-for="status in statusTypes">
                        <el-icon>
                        <component :is="status.icon?status.icon:'DArrowRight'"></component>
                    </el-icon>{{status.label}}
                    </el-dropdown-item>
                </el-dropdown-menu>
            </template>
        </el-dropdown>
    </div>
    <div class="right">
      <el-button type="info" @click="click($event,'search')" v-if="buttons.indexOf('search')>-1">
        <el-icon>
          <component is="Search"></component>
        </el-icon>
      </el-button>
    </div>
  </div>
    `
}

const PostDialog = {
    props: {
        title: {
            type: String,
            default: '新增'
        },
        dialogVisible: {
            type: Boolean,
            default: false
        },
        src: {
            type: String,
            default: ''
        }
    },
    emits: ['update:dialogVisible', 'submit'],
    data() {
        return {
        }
    },
    mounted() {
    },
    computed: {
        outerVisible: {
            get() {
                return this.dialogVisible
            },
            set(newValue) {
                this.$emit('update:dialogVisible', newValue)
            }
        },
    },
    methods: {
        submit() {
            this.$refs.post.validate((valid) => {
                if (valid) {
                    this.$emit('submit', this.post)
                }
            })
        },
        reset() {
            this.$refs.post.resetFields()
        },
        toggle() {
            this.outerVisible = !this.outerVisible
        }
    },
    template: `
    <el-dialog v-model="outerVisible" :title="title" width="80%">
        <iframe class="data-dialog-iframe" :src="src" frameborder="0" width="100%" height="100%"></iframe>
    </el-dialog>
    `
}

export default {
    components: {
        DataSearch, DataToolbar, PostDialog, MfFormItems
    },
    emits: ['show-dialog'],
    props: {
        searchShow: {
            type: Boolean,
            default: true
        },
        searchItems: {
            type: Array,
            default: () => []
        },

        toolbarShow: {
            type: Boolean,
            default: true
        },
        toolbarButtons: {
            type: Array,
            default: () => []
        },
        toolbarAppendButtons: {
            type: Array,
            default: () => []
        },

        showPagination: {
            type: Boolean,
            default: true
        },
        pageSize: {
            type: Number,
            default: 10
        },
        currentPage: {
            type: Number,
            default: 1
        },
        pageSizes: {
            type: Array,
            default: () => [10, 20, 30, 40, 50]
        },

        searchQuery: {
            type: Object,
            default: () => ({})
        },
        pk: {
            type: String,
            default: 'id'
        },
        sortField: {
            type: String,
            default: ''
        },
        sortType: {
            type: String,
            default: 'desc'
        },
        serverBaseUrl: '',
        serverAction: {
            type: Object,
            default: () => {
                return {
                    add: 'add',
                    edit: 'edit',
                    delete: 'delete',
                    read: 'read',
                    batch: 'batch',
                    search: 'search',
                }
            }
        },
        clientBaseUrl: '',
        clientAction: {
            type: Object,
            default: () => {
                return {
                    add: 'add',
                    edit: 'edit',
                }
            }
        },
        selection: {
            type: Boolean,
            default: true
        },
        columns: {
            type: Array,
            default: () => []
        },
        operation: {
            type: Array,
            default: () => []
        },
        query: {
            type: Object,
            default: () => {
                return {}
            }
        },

        dialogWidth: {
            type: String,
            default: '80%'
        },

        formItems: {
            type: Array,
            default: () => []
        },
        formValues: {
            type: Object,
            default: () => {
                return {}
            }
        },
    },
    data() {
        return {
            loading: false,
            data: [],
            query: {},
            showSearch: false,
            total: 100,
            selectedCount: 0,
            currentPageValue: 1,
            pageSizeValue: 10,
            dialogVisible: false,
            dialogIframeSrc: '',
            dialogTitle: '新增',
            formData: {},
            formRules: {},
            formAction: '',
            formDataId: '',
        }
    },
    computed: {
    },
    created() {
        console.log('created', this.pageSize)
        this.query = this.searchQuery;
        this.showSearch = this.searchShow;
        this.currentPageValue = this.currentPage;
        this.pageSizeValue = this.pageSize;
        this.load();
    },
    methods: {
        //加载数据
        async load(query = {}) {
            this.loading = true;
            //暂停1秒
            await new Promise((resolve) => {
                setTimeout(() => resolve(), 200)
            });
            //链接
            let url = '../resources/data/attachment.json';
            //把this.search.query转换成url参数
            let params = new URLSearchParams();
            //query=this.query合并query
            query = Object.assign({}, this.query,
                { page: this.currentPageValue, pageSize: this.pageSizeValue }, query);
            //把query转换成url参数
            for (let key in query) {
                params.append(key, query[key]);
            }
            //把排序参数转换成url参数
            if (params.toString()) {
                url += '?' + params.toString();
            }
            //从服务器加载数据，参数是this.search.query，返回的数据赋值给this.table.data
            axios.get(url).then(res => {
                this.data = res.data;
                this.loading = false;
            }).catch((err) => {
                console.log(err)
                this.loading = false;
                ElMessage.error('加载数据失败');
            });
        },
        //读取一条记录
        async read(id) {
            let url = this.serverBaseUrl + this.serverAction.read;
            let res = await axios.get(url, { params: { id } });
            if (res.data.code == 200) {
                return res.data.data;
            } else {
                ElMessage.error(res.data.message || '读取数据失败');
                return false;
            }
        },
        //提交服务器删除
        delete: function (ids) {
            //提交服务器删除
            let url = this.serverBaseUrl + this.serverAction.delete;
            let data = {};
            data[this.pk] = ids;
            axios.post(url, data).then(res => {
                if (res.data.code == 200) {
                    ElMessage.success('删除成功');
                    this.load();
                } else {
                    ElMessage.error(res.data.message || '删除失败');
                }
            }).catch(err => {
                ElMessage.error('系统错误');
            })
        },
        //批量更新
        batch: function (ids, field, value) {
            //提交到服务器更新
            let url = this.serverBaseUrl + this.serverAction.batch;
            let data = { field, value }
            data[this.pk] = ids;
            axios.post(url, data).then(res => {
                if (res.data.code == 200) {
                    ElMessage.success('更新成功');
                    this.load();
                } else {
                    ElMessage.error(res.data.message || '更新失败');
                }
            }).catch(err => {
                ElMessage.error('系统错误');
            })
        },
        //显示添加数据对话框
        showAddDialog() {
            if (this.formItems.length === 0) {
                this.$emit('show-dialog', 'add');
            } else {
                this.dialogVisible = true;
                this.dialogTitle = '添加';
                this.formAction = 'add';
                this.formDataId = '';
                console.log('showAddDialog  this.dialogVisible', this.dialogVisible)
            }
        },
        showEditDialog(row, index) {
            if (this.formItems.length === 0) {
                this.$emit('show-dialog', 'edit', row, index);
            } else {
                this.formData = row;
                this.dialogVisible = true;
                this.dialogTitle = '编辑';
                this.formAction = 'edit';
                this.formDataId = row[this.pk];
            }
        },
        showViewDialog(row) {
            if (this.formItems.length === 0) {
                this.$emit('show-dialog', 'view', row, index);
            } else {
                this.dialogVisible = true;
                this.dialogTitle = '查看';
                this.formDataId = row[this.pk];
            }
        },
        /**
         * 提交数据 
         * @param {object} formData 
         * @param {string} pkid 
         * @param {string} action 
         */
        submitForm(formData, pkid, action) {
            let url = this.serverBaseUrl + this.serverAction[action];
            if ('edit' == action) {
                formData[this.pk] = pkid;
            }
            return new Promise((resolve, reject) => {
                axios.post(url, formData).then(res => {
                    if (res.data.code == 200) {
                        resolve(res.data);
                    } else {
                        let err = new Error(res.data.message || '提交失败!');
                        reject(err);
                    }
                }).catch(err => {
                    console.log(err);
                    reject(err);
                })
            });

        },
        //获取已选择的数据
        getSelection() {
            return this.$refs.tableRef.getSelectionRows();
        },
        //搜索
        handleSearch() {
            this.currentPageValue = 1;
            this.load();
        },
        //点击工具栏按钮
        handleClickToolbarButton(button, params) {
            let rows = this.getSelection();
            let ids = [];
            if (rows.length > 0) {
                ids = rows.map(item => item[this.pk]);
            }
            switch (button) {
                case 'add':
                    this.showAddDialog();
                    break;
                case 'edit':
                    this.handleViewRow(rows[0], -1);
                    break;
                case 'read':
                    this.handleViewRow(rows[0], -1);
                case 'delete':
                    this.delete(ids);
                    break;
                case 'status':
                    //批量更新状态
                    this.batch(ids, 'status', params.value);
                    break;
                case 'refresh':
                    //刷新
                    this.load();
                    break;
                case 'search':
                    //搜索条显示隐藏
                    this.showSearch = !this.showSearch;
                    break;
                default:
                    break;
            }
        },
        //单页数量改变
        handleSizeChange(val) {
            this.currentPageValue = 1;
            this.load();
        },
        //页码改变
        handleCurrentChange(val) {
            this.load();
        },
        //表格数据选择改变
        handleSelectionChange(selection) {
            this.selectedCount = selection.length;
            console.log('handleSelectionChange', this.selectedCount)
        },
        //删除行
        handleDeleteRow(row, index) {
            let ids = [row[this.pk]];
            this.delete(ids);
        },
        //编辑行
        async handleEditRow(row, index) {
            let formData = await this.read(row[this.pk]);
            if (false === formData) return;
            this.showEditDialog(formData, index);
        },
        //查看详情
        async handleViewRow(row, index) {
            let formData = await this.read(row[this.pk]);
            if (false === formData) return;
            this.showReadDialog(formData, index);
        },
        //提交对话框表单
        handleSubmitDialogForm() {
            this.submitForm(this.formData, this.formDataId, this.formAction).then(() => {
                this.dialogVisible = false;
            }).catch((err) => {
                ElMessage.error(err.message);
            });
        },
    },
    template: `
    <div class="data-manage">
        <DataSearch v-show="showSearch" @search="handleSearch" v-model="query" :items="searchItems"></DataSearch>
        <DataToolbar v-if="toolbarShow" @click="handleClickToolbarButton" :count="selectedCount"></DataToolbar>
        <div class="table-body">
            <el-table border ref="tableRef" style="width: 100%" :selection="selection" v-loading="loading" 
                :data="data" :stripe="true" @selection-change="handleSelectionChange">
                <slot name="table-column">
                    <el-table-column v-if="selection" type="selection" width="55" align="center"></el-table-column>
                    <el-table-column v-for="column in columns" :align="column.align||'left'"
                        :prop="column.prop" :label="column.label" :width="column.width?column.width:'*'" 
                    >
                    <template #default="{row}" v-if="column.type==='image'">
                        <el-image :src="row.filepath" style="width: 100px;"></el-image>
                    </template>
                    </el-table-column>
                    <el-table-column label="操作" width="120" align="center">
                    <template #default="scope">
                    <el-button type="primary" plain circle @click="handleEditRow(scope.row,scope.$index)" 
                        v-if="operation.indexOf('edit')>-1">
                        <el-icon><Edit></Edit></el-icon>
                    </el-button>
                    <el-popconfirm title="确定要删除吗?" @confirm="handleDeleteRow(scope.row,scope.$index)"
                        v-if="operation.indexOf('edit')>-1">
                        <template #reference>
                        <el-button type="danger" plain circle>
                            <el-icon><Delete></Delete></el-icon>
                        </el-button>
                        </template>
                    </el-popconfirm>
                    </template>
                </el-table-column>
                </slot>
            </el-table>
        </div>
        <div v-if="showPagination" class="table-pagination">
            <el-pagination :total="total" 
                :page-sizes="pageSizes" :default-page-size="pageSize" :default-current-page="currentPage" 
                v-model:current-page="currentPageValue" v-model:page-size="pageSizeValue"
                layout="total, sizes, slot, prev, pager, next, jumper"
                @current-change="handleCurrentChange" @size-change="handleSizeChange">
                <template #default>
                    <div style="width:100%"></div>
                </template>
            </el-pagination>
        </div>
        <el-dialog v-model="dialogVisible" :width="dialogWidth" :title="dialogTitle">
            <template #default>
                <el-form ref="formRef" :model="formData" :rules="formRules" label-width="80px">
                    <mf-form-items v-model:values="formData" :items="formItems"></mf-form-items>
                </el-form>
            </template>
            <template #footer>
                <el-button @click="dialogVisible = false">取消</el-button>
                <el-button type="primary" @click="handleSubmitDialogForm">确定</el-button>
            </template>
        </el-dialog>
    </div>
    `,
}