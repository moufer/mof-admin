
import { deepCopy, serverUrl, storageUrl, getThumbByFileType } from "../resources/common/utils.js"
import MfStorageDialog from "./mf-storage-dialog.js"
export default {
    components: {
        MfStorageDialog
    },
    emits: ['update:modelValue'],
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        //上传文件类型
        subType: {
            type: String,
            default: 'image', ////image,audio,video
        },
        //允许选择的文件数量
        limit: {
            type: Number,
            default: 1
        },
        //上传参数配置
        upload: {
            type: Object,
            default: {}
        },
        //媒体文件url前缀
        preUrl: {
            type: String,
            default: ''
        },
    },
    data() {
        return {
            caption: '图片',
            mediaList: [],
            previewList: [],
            uploadProps: {},
            uploading: false,
            fileType: {},
            storageDialogVisible: false,
            playDialogVisible: false,
            playUrl: '',
            PlayType: ''
        }
    },
    computed: {
        value: {
            get: function () {
                return this.modelValue
            },
            set: function (newValue) {
                this.$emit('update:modelValue', newValue)
            }
        }
    },
    watch: {
        modelValue(val) {
            this.mediaList = val ? (this.limit > 1 ? val.split(',') : [val]) : '';
        },
        mediaList(val) {
            this.previewList = val.length > 0 ? val.map(item => this.fullUrl(item)) : [];
        },
        playDialogVisible(val) {
            if (!val) {
                this.playUrl = '';
                this.PlayType = '';
            }
        }
    },
    created() {
        const types = {
            image: { caption: '图片', type: 'image', action: 'image', accept: 'image/*' },
            audio: { caption: '音频', type: 'audio', action: 'media', accept: 'audio/*' },
            video: { caption: '视频', type: 'video', action: 'media', accept: 'video/*' },
            file: { caption: '文件', type: '*', action: 'file', accept: '*/*' }
        }
        this.fileType = types[this.subType];
        this.caption = this.fileType.caption;
        //上传组件配置
        this.uploadProps = deepCopy(this.upload);
        this.uploadProps['before-upload'] = (file) => {
            if (this.mediaList.length >= this.limit) {
                this.$message.error('文件数量不能超过' + this.limit + '张');
                return false;
            }
        };
        this.uploadProps['on-progress'] = (UploadProgressEvent) => {
            if (!this.uploading) this.uploading = true;
            console.log(UploadProgressEvent);
        };
        this.uploadProps['on-success'] = (res) => {
            console.log(res);
            this.uploading = false;
            if (res.errcode !== 0) {
                this.$message.error(res.errmsg);
            } else {
                this.selectItem([res.data.path]);
            }
        };
        this.uploadProps['on-error'] = (err) => {
            console.log(err);
            this.uploading = false;
            this.$message.error('文件上传错误');
        };
        this.uploadProps['show-file-list'] = false;
        if (typeof this.uploadProps['action'] === 'undefined') {
            this.uploadProps['action'] = serverUrl('/admin/upload/' + this.fileType.action);
        }
        if (typeof this.uploadProps['headers'] === 'undefined') {
            this.uploadProps['headers'] = {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
            };
        }
        if (typeof this.uploadProps['accept'] === 'undefined') {
            this.uploadProps['accept'] = this.fileType.accept;
        }
        //mediaList初始化
        this.mediaList = this.value ? (this.limit > 1 ? this.value.split(',') : [this.value]) : '';
    },
    methods: {
        deleteItem(index) {
            this.mediaList.splice(index, 1);
            this.value = this.mediaList.join(',');
        },
        fullUrl(path) {
            if (typeof path !== 'string') {
                path = '';
            }
            if (path.substring(0, 4) == 'http') {
                return path;
            }
            return storageUrl(path);
        },
        selectItem(items) {
            if (this.mediaList.length === 0) {
                this.mediaList = [];
            }
            this.mediaList.push(...items);
            if (this.mediaList.length >= this.limit) {
                this.mediaList.splice(0, this.mediaList.length - this.limit)
            }
            this.value = this.mediaList.join(',');
        },
        showPlayBox(index) {
            this.playDialogVisible = true
            this.playUrl = this.previewList[index];
            console.log('playVideo', this.playUrl);
        },
        download(index) {
            const url = this.previewList[index];
            if (url) {
                const a = document.createElement('a');
                a.target = '_blank';
                a.href = url;
                a.download = url.substring(url.lastIndexOf('/') + 1);
                a.click();
            }
        },
        getThumb(url, type) {
            return getThumbByFileType(url, type);
        }
    },
    template:/*html*/`
    <div style="width:100%;">
        <div class="mf-image-selector">
            <div class="mf-image-selector-header">
                <el-input v-model="value" :placeholder="'请选择或上传'+caption" />
                <el-button @click="storageDialogVisible=true" style="margin-right:5px">选择{{caption}}</el-button>
                <el-upload v-bind="uploadProps">
                    <el-button type="primary" :loading="uploading">上传{{caption}}</el-button>
                </el-upload>
            </div>
            <div class="mf-image-selector-preview">
                <div class="mf-image-selector-item" v-for="(url,index) in mediaList">
                    <div class="mf-image-selector-preview-item" v-if="subType === 'image'">
                        <el-image :src="fullUrl(url)" fit="contain" :preview-src-list="previewList" />
                    </div>
                    <div class="mf-image-selector-preview-item" v-if="['audio','video'].indexOf(subType)>-1">
                        <el-image :src="'/resources/images/file-'+subType+'.png'" fit="contain" @click="showPlayBox(index)" />
                    </div>
                    <div class="mf-image-selector-preview-item" v-if="subType === 'file'">
                        <el-image src="/resources/images/file-other.png" fit="contain" @click="download(index)" />
                    </div>
                    <el-button size="small" type="danger" @click="deleteItem(index)"
                        class="mf-image-selector-item__close">删除</el-button>
                </div>
            </div>
        </div>
        <el-dialog v-model="playDialogVisible" width="600" title="播放" style="text-algin: center">
            <audio :src="playUrl" controls v-if="subType === 'audio'" style="width:100%;"></audio>
            <video :src="playUrl" controls v-if="subType === 'video'" style="max-height:600px;"></video>
        </el-dialog>
        <mf-storage-dialog 
                v-if="storageDialogVisible" 
                @on-select="selectItem" 
                @on-close="storageDialogVisible=false"
                :fileType="fileType" 
                :limit="limit"
        />
    </div>
    `
}