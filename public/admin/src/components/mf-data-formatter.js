import { get } from "lodash-es";
import moment from "moment";
import {
  storageUrl,
  getFileTypeByFileName,
  getThumbByFileType,
} from "/src/utils/index.js";
import MfDataPreview from "/src/components/mf-data-preview.js";
export default {
  components: {
    MfDataPreview,
  },
  props: {
    column: {
      type: Object,
      required: true,
    },
    row: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      type: "",
    };
  },
  created() {
    this.type = this.column.type;
  },
  computed: {
    value() {
      return this.getValue();
    },
    formatValue() {
      return this.formatter();
    },
  },
  methods: {
    /**
     * 获取指定行指定列的值。
     * @param {object} row 数据对象
     * @param {object} column 字段配置对象
     * @returns {any} 字段值
     */
    getValue() {
      const propName = this.column.prop || this.column.propAlias;
      return get(this.row, propName);
    },

    /**
     * 根据指定的类型和列配置格式化给定的值。
     */
    formatter() {
      switch (this.type) {
        case "select":
          let options =
            typeof this.column.options === "function"
              ? column.options()
              : this.column.options;
          if (Array.isArray(this.value)) {
            return options
              .filter((item) => this.value.indexOf(item.value) > -1)
              .map((item) => item.label)
              .join("，");
          } else {
            return (
              options.find((item) => item.value === this.value)?.label ||
              this.value
            );
          }
        case "date":
          return this.value
            ? moment(this.value).format(this.column.format || "YYYY-MM-DD")
            : this.value;
        case "datetime":
          return this.value
            ? moment(this.value).format(
                this.column.format || "YYYY-MM-DD HH:mm:ss"
              )
            : this.value;
        case "time":
          return this.value
            ? moment(this.value).format(this.column.format || "HH:mm:ss")
            : this.value;
        case "image":
          return this.value
            ? storageUrl(this.value)
            : assetUrl("images/no-image.png");
        case "images":
          const images = this.value.split(",");
          return images.map((img) => storageUrl(img));
        case "icon":
          return !this.value || this.value.indexOf(" ") > -1
            ? "Sugar"
            : this.value;
        case "media":
          let fileType = this.row[this.column.media_type_prop || "other"];
          fileType = fileType || getFileTypeByFileName(this.value, fileType);
          return {
            url: storageUrl(this.value),
            type: fileType,
            cover: getThumbByFileType(this.value, fileType),
          };
        case "storage":
          return this.value ? storageUrl(this.value) : "#";
        case "boolean":
          return this.value ? "是" : "否";
        case "tag":
          if (this.column.tags && this.column.tags.length > 0) {
            return this.column.tags.filter(
              (item) => this.value.indexOf(item) >= 0
            );
          }
          return this.column.options.filter(
            (item) => this.value.indexOf(item.this.value) >= 0
          );
        default:
          return this.value;
      }
    },
  },
  template: /*html*/ `
    <div v-if="type==='image' || type==='images'">
      <mf-data-preview :src="formatValue" :type="type" :cover="formatValue" />
    </div>
    <div v-else-if="type==='media'">
        <mf-data-preview :src="formatValue.url" :type="formatValue.type" :cover="formatValue.cover" />
    </div>
    <div v-else-if="type==='icon'">
        <el-icon style="font-size:24px;"><component :is="formatValue" /></el-icon>
    </div>
    <div v-else-if="type==='avatar'">
        <el-avatar :src="formatValue"></el-avatar>
    </div>
    <div v-else-if="type==='tag'">
        <el-tag round v-for="tag in formatValue" style="margin:0 1px;">{{tag.label}}</el-tag>
    </div>
    <div v-else-if="type==='storage'">
        <el-link :href="formatValue" target="_blank">打开文件</el-link>
    </div>
    <span v-else>{{formatValue}}</span>
  `,
};
