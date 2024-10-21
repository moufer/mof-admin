import { deepCopy, getThumbByFileType, serverUrl } from "comm/utils.js";
import { ElMessage } from "element-plus";
export default {
  inject: ["http"],
  emits: ["update:modelValue", "on-select", "on-close"],
  props: {
    modelValue: {
      type: [String, Number],
      default: () => "",
    },
    fileType: {
      type: Object,
      default: {},
    },
    limit: {
      type: Number,
      default: 1,
    },
    //上传参数配置
    upload: {
      type: Object,
      default: {},
    },
  },
  data() {
    return {
      items: [],
      selectIds: [],
      selectItems: [],
      caption: "",
      keyword: "",
      itemTotal: 0,
      currentPage: 1,
      dialogVisible: false,
      activeTab: "select",
      uploading: false,
      uploadProps: {},
      uploadProgress: { total: 0, uploaded: 0, percent: 0 },
    };
  },
  watch: {
    dialogVisible(val) {
      if (!val) this.$emit("on-close");
    },
  },
  created() {
    this.caption = this.fileType.caption;
    this.dialogVisible = true;

    this.getData();
    this.initUpload();
  },
  methods: {
    initUpload() {
      //上传配置
      this.uploadProps = deepCopy(this.upload);
      this.uploadProps.multiple = true; //允许多选
      this.uploadProps.limit = 9; // 单次上传限制
      this.uploadProps["on-progress"] = (progress, file, files) => {
        console.log("on-progress", progress, file, files);
        if (!this.uploading) this.uploading = true;
        this.uploadProgress.total = files.length;
        this.uploadProgress.uploaded = files.filter(
          (item) => item.status === "success"
        ).length;
        this.uploadProgress.percent = progress.percent;
      };
      this.uploadProps["on-success"] = (res, file, files) => {
        console.log("on-success", res, file, files);
        this.uploadProgress = { total: 0, uploaded: 0, percent: 0 };
        this.uploading = false;
        if (res.errcode !== 0) {
          this.$message.error(res.errmsg);
        } else if (
          files.filter((item) => item.status !== "success").length === 0
        ) {
          this.currentPage = 1;
          this.keyword = "";
          this.getData();
          //this.selectItem([res.data.path]);
          this.$message.success("文件上传成功");
          this.$refs.uploadRef.clearFiles();
        }
      };
      this.uploadProps["on-error"] = (err) => {
        console.log(err);
        this.uploading = false;
        this.$message.error("文件上传错误");
      };
      this.uploadProps["show-file-list"] = false;
      if (typeof this.uploadProps["action"] === "undefined") {
        this.uploadProps["action"] = serverUrl(
          "/system/upload/" + this.fileType.action
        );
      }
      if (typeof this.uploadProps["headers"] === "undefined") {
        this.uploadProps["headers"] = {
          Authorization: "Bearer " + localStorage.getItem("admin_token"),
        };
      }
      if (typeof this.uploadProps["accept"] === "undefined") {
        this.uploadProps["accept"] = this.fileType.accept;
      }
    },

    getData() {
      let url = serverUrl("/system/storage/selector");
      let params = new URLSearchParams();
      params.append(
        "params[file_type]",
        this.fileType.type === "*" ? "" : this.fileType.type
      );
      params.append("params[title]", this.keyword);
      params.append("order[field]", "id");
      params.append("order[sort]", "desc");
      params.append("page", this.currentPage);
      params.append("page_size", 10);
      url += "?" + params.toString();

      this.http
        .get(url)
        .then((res) => {
          this.items = res.data.data;
          this.itemTotal = res.data.total;
          this.selectIds = [];
        })
        .catch((err) => {
          err.errmsg && ElMessage.error(err.errmsg);
        });
    },

    handleSearch() {
      this.currentPage = 1;
      this.getData();
    },

    handleChangePage(pageNumber) {
      this.currentPage = pageNumber;
      this.getData();
    },

    handleConform() {
      if (this.selectIds.length === 0) {
        ElMessage.error("未选择" + this.caption);
        return;
      }
      let urls = [];
      this.selectIds.forEach((itemId) => {
        urls.push(this.items.find((item) => item.id === itemId).path);
      });

      this.$emit("on-select", urls);
      this.dialogVisible = false;
    },

    handleSelectItem(itemId) {
      const index = this.selectIds.indexOf(itemId);
      if (index > -1) {
        this.selectIds.splice(index, 1);
      } else {
        if (this.limit <= this.selectIds.length) {
          this.selectIds.pop();
        }
        this.selectIds.push(itemId);
      }
    },

    handleUpload() {
      //this.$emit('on-upload');
    },

    getThumb(url, type) {
      return getThumbByFileType(url, type);
    },
  },
  template: /*html*/ `
    <div class="mf-storage-dialog">
        <el-dialog v-model="dialogVisible" :title="caption" width="800" :lock-scroll="false">
            <el-card shadow="never">
                <template #header>
                    <el-row :gutter="10">
                        <el-col :span="18">
                            <el-input v-model="keyword" placeholder="请输入关键字"
                                clearable @keyup.enter="handleSearch" />
                        </el-col>
                        <el-col :span="2" style="text-align:right;">
                            <el-button type="default" @click="handleSearch">搜索</el-button>
                        </el-col>
                        <el-col :span="4" style="text-align:right;">
                            <el-upload ref="uploadRef" v-bind="uploadProps">
                                <el-button style="width:100%;" type="primary" :loading="uploading">上传{{caption}}</el-button>
                            </el-upload>
                        </el-col>
                    </el-row>
                </template>
                <div class="mf-storage-dialog-items" style="min-height:320px;">
                    <div style="text-align:center;margin-top:100px;width:100%;" v-if="items.length===0">
                        没有找到相关数据
                    </div>
                    <div class="mf-storage-dialog-item" v-for="(item, index) in items" :key="index">
                        <div 
                            :class="{'mf-storage-dialog-item-img':true,'select':selectIds.indexOf(item.id)>-1}"
                            @click="handleSelectItem(item.id)"
                        >
                            <img :src="getThumb(item.url,item.file_type)" class="image" fit="contain" />
                        </div>
                        <el-text size="small" truncated>{{item.title}}</el-text>
                    </div>
                </div>
                <template #footer>
                    <div class="mf-storage-dialog-footer">
                        <div></div>
                        <el-pagination
                            background
                            layout="prev, pager, next"
                            :total="itemTotal"
                            @current-change="handleChangePage"
                        />
                    </div>
                </template>
            </el-card>
            <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible=false" size="large">取消</el-button>
                <el-button type="primary" @click="handleConform" size="large">确定</el-button>
            </div>
            </template>
        </el-dialog>
    </div>
    `,
};
