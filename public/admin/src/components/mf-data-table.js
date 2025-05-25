import _ from "lodash-es";
import { isEmpty, evaluateExpression } from "/src/utils/index.js";
import MfDataFormatter from "/src/components/mf-data-formatter.js";
export default {
  name: "mf-data-table",
  components: { MfDataFormatter },
  props: {
    data: {
      type: Array,
      default: () => [],
    },
    total: {
      type: Number,
      default: 0,
    },
    pageNum: {
      type: Number,
      default: 1,
    },
    pageSize: {
      type: Number,
      default: 10,
    },
    pageSizes: {
      type: Array,
      default: () => [10, 20, 50, 100],
    },
    columns: {
      type: Array,
      default: () => [],
    },
    selection: {
      type: Boolean,
      default: false,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    showPagination: {
      type: Boolean,
      default: true,
    },
    operation: {
      type: Object,
      default: () => {
        return {
          show: true,
          label: "操作",
          width: 150,
          buttons: ["detail", "edit", "delete"],
        };
      },
    },
  },
  emits: [
    "selection-change",
    "pagination-change",
    "operation-click",
    "update:pageNum",
    "update:pageSize",
  ],
  computed: {
    currentPageValue: {
      get() {
        return this.pageNum;
      },
      set(val) {
        this.$emit("update:pageNum", val);
      },
    },
    pageSizeValue: {
      get() {
        return this.pageSize;
      },
      set(val) {
        this.$emit("update:pageSize", val);
      },
    },
  },
  methods: {
    /**
     * 获取已选数据数组
     * @param {string} key
     * @returns Array
     */
    getSelection(key = "*") {
      let rows = this.$refs.tableRef.getSelectionRows();
      if (key !== "*") {
        key === "__PK__" && (key = this.transmitHandle.pk);
        return rows.map((row) => row[key]);
      }
      return rows;
    },
    handleSelectionChange(val) {
      this.$emit("selection-change", val);
    },
    handleCurrentChange(val) {
      this.$emit("pagination-change", {
        pageNum: val,
        pageSize: this.pageSizeValue,
      });
    },
    handleSizeChange(val) {
      this.$emit("pagination-change", {
        pageNum: this.currentPageValue,
        pageSize: val,
      });
    },
    handelOperationClick(button, row, index) {
      this.$emit("operation-click", button.name, row, index, button);
    },
    /**
     * 根据提供的参数确定操作按钮的可见性可用性。
     *
     * @param expr
     * @param {Object} row - 行对象。
     * @param {number} index - 按钮的索引。
     * @param key
     * @return {boolean} - 如果按钮应该可见，则返回true；否则返回false。
     */
    buttonExpr(expr, row, index, key) {
      let type = typeof expr;
      if (type === "undefined" || isEmpty(row)) {
        return false;
      }
      if (type === "function") {
        return expr(row, index);
      } else if (type === "boolean") {
        return expr;
      } else if (type === "string") {
        //通过表达式来判断是否显示
        return evaluateExpression(expr, row);
      }
      return true;
    },
  },
  template: /*html*/ `
    <el-table border stripe ref="tableRef" style="width:100%" v-loading="loading" table-layout="fixed" 
        row-key="id" :selection="selection" :data="data" @selection-change="handleSelectionChange" 
    >
        <el-table-column v-if="selection" type="selection" width="55" align="center"></el-table-column>
        <slot name="columns">
            <template v-for="column in columns">
            <el-table-column v-if="column.visible" :align="column.align||'left'"
                :prop="column.prop" :label="column.label" :width="column.width||'*'"
                :filters="column.filters" :formatter="column.formatter">
                <template #default="{row}">
                    <mf-data-formatter :column="column" :row="row" />
                </template>
            </el-table-column>
            </template>
        </slot>
        <el-table-column v-if="operation.show" :fixed="operation.fixed||false"  
            :label="operation.label||操作" :width="operation.width||120" :align="operation.align||'center'">
            <template #default="scope">
                <slot name="operate" :row="scope.row" :$index="scope.$index" :column="scope.column">
                    <template v-for="button in operation.buttons">
                        <el-popconfirm v-if="button.confirm && buttonExpr(button.visible||true, scope.row, scope.$index)" 
                            :title="button.confirm.title||'您确定要进行此操作吗?'" 
                            @confirm="handelOperationClick(button, scope.row, scope.$index)">
                            <template #reference>
                                <el-button plain 
                                    :disabled="buttonExpr(button.disable||false, scope.row, scope.$index,'disable')"
                                    :type="button.theme||'danger'" size="small" :title="button.label">
                                    {{button.label}}
                                </el-button>
                            </template>
                        </el-popconfirm>
                        <el-button v-else-if="buttonExpr(button.visible||true, scope.row, scope.$index)" 
                            plain 
                            :disabled="buttonExpr(button.disable||false, scope.row, scope.$index,'disable')"
                            size="small" :type="button.theme||'primary'" :title="button.label||''"
                            @click="handelOperationClick(button, scope.row, scope.$index)">
                            {{button.label}}
                        </el-button>
                    </template>
                </slot>
            </template>
        </el-table-column>
    </el-table>
    <div v-if="showPagination" class="table-pagination">
        <el-pagination :total="total" 
            :page-sizes="pageSizes" :default-page-size="pageSize" :default-current-page="pageNum" 
            v-model:current-page="currentPageValue" v-model:page-size="pageSizeValue"
            layout="total, sizes, slot, prev, pager, next, jumper"
            @current-change="handleCurrentChange" @size-change="handleSizeChange">
            <template #default>
                <div style="width:100%"></div>
            </template>
        </el-pagination>
    </div>
  `,
};
