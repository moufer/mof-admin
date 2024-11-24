import {
  isEmpty,
  evaluateExpression,
  clientUrl,
  getThumbByFileType,
} from "comm/utils.js";
export default {
  name: "mf-data-table",
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
     * @param {Object} button - 操作按钮对象。
     * @param {Object} row - 行对象。
     * @param {number} index - 按钮的索引。
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
        //console.log('operationButtonVisible', expr, row, index)
        //通过表达式来判断是否显示
        return evaluateExpression(expr, row);
      }
      return true;
    },
    /**
     * 根据指定的类型和列配置格式化给定的值。
     *
     * @param {string} type - 要应用的格式化类型。
     * @param {object} column - 列的配置对象。
     * @param {any} value - 要格式化的值。
     * @param {object} row - 行的数据对象。
     * @return {any} 格式化后的值。
     */
    formatter(type, column, value, row) {
      switch (type) {
        case "select":
          if (typeof column.options === "function") {
            column.options = column.options();
          }
          //判断value是不是一个数组
          if (Array.isArray(value)) {
            return column.options
              .filter((item) => value.indexOf(item.value) > -1)
              .map((item) => item.label)
              .join("，");
          } else {
            return (
              column.options.find((item) => item.value === value)?.label ||
              value
            );
          }
        case "date":
          return value
            ? moment(value).format(column.format || "YYYY-MM-DD")
            : value;
        case "datetime":
          return value
            ? moment(value).format(column.format || "YYYY-MM-DD HH:mm:ss")
            : value;
        case "time":
          return value
            ? moment(value).format(column.format || "HH:mm:ss")
            : value;
        case "image":
          return value ? value : clientUrl("/resources/images/no-image.png");
        case "icon":
          return !value || value.indexOf(" ") > -1 ? "Sugar" : value;
        case "media":
          return getThumbByFileType(
            row[column.prop],
            row[column.media_type_prop || "other"] || "other"
          );
        case "boolean":
          return value ? "是" : "否";
        case "tag":
          if (column.tags && column.tags.length > 0) {
            return column.tags.filter((item) => value.indexOf(item) >= 0);
          }
          return column.options.filter(
            (item) => value.indexOf(item.value) >= 0
          );
        default:
          return value;
      }
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
                <template #default="{row}" v-if="column.type==='image'">
                    <el-image :src="formatter('image', column, row[column.propAlias||column.prop])" 
                        style="width:auto;" :referrerPolicy="column.referrerPolicy||'no-referrer-when-downgrade'"></el-image>
                </template>
                <template #default="{row}" v-if="column.type==='select'">
                    {{formatter('select', column, row[column.propAlias||column.prop])}}
                </template>
                <template #default="{row}" v-if="column.type==='icon'">
                    <el-icon style="font-size:24px;">
                        <component :is="formatter('icon', column, row[column.propAlias||column.prop])" />
                    </el-icon>
                </template>
                <template #default="{row}" v-if="column.type==='media'">
                    <el-image :src="formatter('media', column, row[column.propAlias||column.prop], row)" 
                        style="width:auto;max-height:100px;" fit="scale-down"></el-image>
                </template>
                <template #default="{row}" v-if="column.type==='avatar'">
                    <el-avatar :src="formatter('image', column, row[column.propAlias||column.prop])">
                    </el-avatar>
                </template>
                <template #default="{row}" v-if="column.type==='boolean'">
                    {{formatter('boolean', column, row[column.propAlias||column.prop])}}
                </template>
                <template #default="{row}" v-if="column.type==='tag'">
                    <el-tag round v-for="tag in formatter('tag', column, row[column.propAlias||column.prop])" 
                        style="margin:0 1px;">{{tag.label}}</el-tag>
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
