export default {
    name: 'mf-data-toolbar',
    props: {
        selectionCount: {
            type: Number,
            default: 0
        },
        buttons: {
            type: Array,
            default: () => ['add', 'delete', 'status', 'refresh', 'search']
        },
        statusTypes: {
            type: Array,
            default: () => [
                { label: '启用', value: 1, icon: 'View' },
                { label: '禁用', value: 0, icon: 'Hide' }
            ]
        },
    },
    methods: {
        handleCheckItem: function (item) {
            if ('delete' === item) {
                this.$confirm(`当前操作将删除 ${this.selectionCount} 条数据, 确定删除吗?`, {
                    type: 'warning'
                }).then(() => {
                    this.$emit('click', 'delete')
                }).catch(() => { });
            } else {
                this.$emit('click', item)
            }
        },
        handleCheckStatus: function (command) {
            this.$emit('click', 'status', { value: command })
        }
    },
    template: /*html*/`
    <div class="table-toolbar">
    <div class="left">
      <el-button type="success" @click="handleCheckItem('refresh')" icon="Refresh" />
      <el-button icon="Plus" type="primary" @click="handleCheckItem('add')" 
        v-if="buttons.indexOf('add')>-1" title="添加">添加</el-button>
      <el-button icon="Edit" type="warning" @click="handleCheckItem('edit')" :disabled="selectionCount==0"
        v-if="buttons.indexOf('edit')>-1" title="编辑">编辑</el-button>
      <el-button icon="Delete" type="danger" @click="handleCheckItem('delete')" :disabled="selectionCount==0"
        v-if="buttons.indexOf('delete')>-1" title="删除">删除</el-button>
      <el-dropdown @command="handleCheckStatus" v-if="buttons.indexOf('status')>-1" title="更新状态">
        <el-button icon="Open" type="warning" :disabled="selectionCount==0">状态</el-button>
            <template #dropdown>
                <el-dropdown-menu>
                    <el-dropdown-item :command="status.value" v-for="status in statusTypes">
                        <el-icon>
                            <component :is="status.icon?status.icon:'DArrowRight'"></component>
                        </el-icon>
                        {{status.label}}
                    </el-dropdown-item>
                </el-dropdown-menu>
            </template>
        </el-dropdown>
        <slot name="left" :selection-count="selectionCount"></slot>
    </div>
    <div class="right">
      <slot name="right" :selection-count="selectionCount"></slot>
      <el-button plain type="info" @click="handleCheckItem('search')" icon="Search" title="搜索"
        v-if="buttons.indexOf('search')>-1" />
    </div>
  </div>
    `
}