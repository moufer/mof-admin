import { serverUrl, clientUrl, storageUrl } from "comm/utils.js";
import { VueUeditorWrap } from "../resources/libraries/vue-ueditor-wrap@3.0.8/es/vue-ueditor-wrap-esm.js";
import MfStorageDialog from "./mf-storage-dialog.js";
export default {
  components: {
    VueUeditorWrap,
    MfStorageDialog,
  },
  emits: ["update:modelValue"],
  props: {
    config: {
      type: Object,
      default: () => {
        return {};
      },
    },
    modelValue: {
      type: String,
      default: "",
    },
  },
  computed: {
    value: {
      get() {
        return this.modelValue;
      },
      set(newValue) {
        this.$emit("update:modelValue", newValue);
      },
    },
  },
  data: () => {
    return {
      editor: null,
      editorConfig: {},
      storageDialogVisible: false,
      storageFileTypes: {},
      storageFileType: "image",
    };
  },
  created() {
    //初始化
    this.init();
  },
  methods: {
    //初始化
    init() {
      //获取div（class=el-overlay）层zIndex数值，用于编辑器zIndex的基数
      let zIndex = 2012;
      document.querySelectorAll(".el-overlay").forEach((item) => {
        if (item.style.zIndex > zIndex) {
          zIndex = item.style.zIndex;
        }
      });

      this.editorConfig = {
        ...this.config,
        ...{
          UEDITOR_HOME_URL: clientUrl("/resources/libraries/ueditor-plus/"),
          UEDITOR_CORS_URL: clientUrl("/resources/libraries/ueditor-plus/"),
          serverUrl: serverUrl("/system/ueditor/index"),
          zIndex,
        },
      };

      this.storageFileTypes = {
        image: {
          caption: "图片",
          type: "image",
          action: "image",
          accept: "image/*",
        },
        audio: {
          caption: "音频",
          type: "audio",
          action: "media",
          accept: "audio/*",
        },
        video: {
          caption: "视频",
          type: "video",
          action: "media",
          accept: "video/*",
        },
        file: { caption: "附件", type: "*", action: "file", accept: "*/*" },
      };
    },

    //上传图片按钮注册
    registerUploadImageBtn(editorId) {
      window.UE.registerUI(
        "mf-image-upload",
        (editor, uiName) => {
          const iconUrl = clientUrl(
            "/resources/libraries/ueditor-plus/themes/default/images/mf-image-upload.png"
          );
          const btn = new window.UE.ui.Button({
            name: uiName,
            title: "上传图片",
            cssRules: `background-image: url('${iconUrl}') !important; background-size: 20px;`,
            onclick: () => {
              this.storageDialogVisible = true;
              this.storageFileType = this.storageFileTypes["image"];
            },
          });
          this.listenerBtnStatus(editor, uiName, btn);
          return btn;
        },
        55, //位置序号
        editorId
      );
    },

    //上传音频按钮注册
    registerUploadAudioBtn(editorId) {
      window.UE.registerUI(
        "mf-audio-upload",
        (editor, uiName) => {
          const iconUrl = clientUrl(
            "/resources/libraries/ueditor-plus/themes/default/images/mf-audio-upload.png"
          );
          const btn = new window.UE.ui.Button({
            name: uiName,
            title: "上传音频",
            cssRules: `background-image: url('${iconUrl}') !important; background-size: 18px;`,
            onclick: () => {
              this.storageDialogVisible = true;
              this.storageFileType = this.storageFileTypes["audio"];
            },
          });
          this.listenerBtnStatus(editor, uiName, btn);
          return btn;
        },
        55, //位置序号
        editorId
      );
    },

    //上传视频按钮注册
    registerUploadVideoBtn(editorId) {
      window.UE.registerUI(
        "mf-video-upload",
        (editor, uiName) => {
          const iconUrl = clientUrl(
            "/resources/libraries/ueditor-plus/themes/default/images/mf-video-upload.png"
          );
          const btn = new window.UE.ui.Button({
            name: uiName,
            title: "上传视频",
            cssRules: `background-image: url('${iconUrl}') !important; background-size: 18px;`,
            onclick: () => {
              this.storageDialogVisible = true;
              this.storageFileType = this.storageFileTypes["video"];
            },
          });
          this.listenerBtnStatus(editor, uiName, btn);
          return btn;
        },
        55, //位置序号
        editorId
      );
    },

    //监听按钮状态
    listenerBtnStatus(editor, uiName, btn) {
      // 当点到编辑内容上时，按钮要做的状态反射
      editor.addListener("selectionchange", function () {
        const state = editor.queryCommandState(uiName);
        if (state === -1) {
          btn.setDisabled(true);
          btn.setChecked(false);
        } else {
          btn.setDisabled(false);
          btn.setChecked(state);
        }
      });
    },

    onEditorReady(editor) {
      this.editor = editor;
    },

    onEditorBeforeInit(editorId) {
      this.registerUploadAudioBtn(editorId);
      this.registerUploadVideoBtn(editorId);
      this.registerUploadImageBtn(editorId);
    },

    onSelectStorageItem(items) {
      if (this.editor) {
        //数据插入到编辑器中
        items.forEach((item) => {
          let content = storageUrl(item);
          switch (this.storageFileType.type) {
            case "image":
              content = `<img src="${content}" style="max-width:100%" />`;
              break;
            case "audio":
              content = `<audio class="edui-audio-audio" src="${content}" controls="controls" />`;
              break;
            case "video":
              content = `<video src="${content}" controls="controls" style="max-width:100%" />`;
              break;
            default:
              //获取item里的文件名
              let title = new URL(content).pathname.split("/").pop();
              content = `<a href="${content}" target="_blank">${title}</a>`;
          }
          this.editor.execCommand("inserthtml", content);
        });
      }
    },
  },
  template: /*html*/ `
        <div style="width:100%;">
            <vue-ueditor-wrap v-model="value" :config="editorConfig" 
                @ready="onEditorReady"  @before-init="onEditorBeforeInit"
            />
            <mf-storage-dialog 
                v-if="storageDialogVisible" 
                @on-select="onSelectStorageItem" 
                @on-close="storageDialogVisible=false"
                :fileType="storageFileType"
                :limit="99"
            />
        </div>
    `,
};
