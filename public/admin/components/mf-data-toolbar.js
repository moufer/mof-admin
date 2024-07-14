export default {
    name: 'mf-data-toolbar',
    props: {
        selectionCount: {
            type: Number,
            default: 0
        },
        buttons: {
            type: Array,
            default: () => ['refresh', 'add', 'delete', 'status', 'search']
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
                this.$confirm(`此操作将永久删除 ${this.selectionCount} 条数据, 是否继续?`, {
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
      <el-button type="success" @click="handleCheckItem('refresh')" v-if="buttons.indexOf('refresh')>-1" title="刷新">
        <el-icon><component is="Refresh"></component></el-icon>
      </el-button>
      <el-button type="primary" @click="handleCheckItem('add')" v-if="buttons.indexOf('add')>-1" title="新增">
        <el-icon><component is="Plus"></component></el-icon>
      </el-button>
      <el-button type="warning" @click="handleCheckItem('edit')" :disabled="selectionCount==0"
        v-if="buttons.indexOf('edit')>-1" title="编辑">
        <el-icon><component is="Edit"></component></el-icon>
      </el-button>
      <el-button type="danger" @click="handleCheckItem('delete')" :disabled="selectionCount==0"
        v-if="buttons.indexOf('delete')>-1" title="删除">
        <el-icon><component is="Delete"></component></el-icon>
      </el-button>
      <el-dropdown @command="handleCheckStatus" v-if="buttons.indexOf('status')>-1" title="更新状态">
        <el-button type="warning" :disabled="selectionCount==0">
            <el-icon><component is="Open"></component></el-icon>
        </el-button>
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
      <el-button type="info" @click="handleCheckItem('search')" v-if="buttons.indexOf('search')>-1">
        <el-icon><component is="Search"></component></el-icon>
      </el-button>
    </div>
  </div>
    `
}