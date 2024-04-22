import MfKeyValue from './mf-key-value.js';
import MfXselect from './mf-xselect.js';
import MfIconSelector from './mf-icon-selector.js';
import { ElMessage } from 'element-plus';
import { isEmpty, deepCopy } from 'comm/utils.js';

export default {
    name: 'mf-form-item',
    components: {
        MfKeyValue, MfXselect, MfIconSelector
    },
    emits: ['update:value'],
    props: {
        item: {
            type: Object,
            required: true
        },
        value: {
            type: [Object, Array, String, Number, Boolean],
            default: () => {
                return {}
            }
        }
    },
    data() {
        return {
            form: {},
            headers: {},
            fileList: [],
            previewImageDialogImageUrl: '',
            previewImageDialogVisible: false,
        }
    },
    created() {
        this.init();
    },
    computed: {
        formValue: {
            get: function () {
                return this.value
            },
            set: function (newValue) {
                this.$emit('update:value', newValue)
            }
        }
    },
    methods: {
        getData() {
            return this.form;
        },
        init() {
            if (this.item.type?.startsWith('upload:')) {
                this.headers.Authorization = localStorage.getItem('admin_token');
                if (typeof this.item.headers === 'object') {
                    //合并 this.headers 和 this.item.headers
                    this.headers = Object.assign(this.headers, this.item.headers);
                }
                if (isEmpty(this.formValue)) {
                    this.formValue = [];
                } else {
                    this.fileList = deepCopy(this.formValue);
                }
            }
            this.initRules();
        },
        initRules() {
            if (this.item.rules && this.item.rules.length) {
                this.item.rules.forEach(rule => {
                    //补充message内容
                    if (!rule.message) {
                        if (rule.required === true) {
                            rule.message = `${this.item.label}不能为空`
                        } else {
                            rule.message = `${this.item.label}格式不正确`
                        }
                    }
                })
            }
        },
        beforeUpload(rawFile) {
            let size = rawFile.size; //字节
            let ext = rawFile.name.split('.').pop().toLowerCase(); //后缀
            if (this.item.limit_size > 0 && size > this.item.limit_size) { //大小
                ElMessage.error(`文件大小超过了最大限制`);
                return false;
            } else if (!isEmpty(this.item.limit_ext)) { //扩展名
                let exts = this.item.limit_ext.split(',').map(item => item.toLowerCase());
                if (!exts.includes(ext)) {
                    ElMessage.error(`不支持此文件格式`);
                    return false;
                }
            }
            return true;
        },
        uploadExceed(files, fileList, e) {
            ElMessage.error(`超出了上传数量`);
        },
        uploadSuccess(res, file, fileList) {
            if (res.errcode !== 0) {
                this.$refs.upload.handleRemove(file);
                ElMessage({ message: res.errmsg, type: 'error' });
            } else {
                if (fileList.length > 0) {
                    this.formValue = fileList.map(item => {
                        return { path: item.response?.data.path }
                    });
                } else {
                    this.formValue = [];
                }
            }
        },
        uploadRemove(uploadFile, uploadFiles) {
            if (uploadFiles.length > 0) {
                this.formValue = uploadFiles.map(item => {
                    return { path: item.response?.data.path }
                });
            } else {
                this.formValue = [];
            }
        },
        uploadPreview(file) {
            this.previewImageDialogImageUrl = file.url;
            this.previewImageDialogVisible = true;
        },
        uploadError(error, uploadFile, uploadFiles) {
            ElMessage({
                message: error.errmsg || '上传失败',
                type: 'error',
            });
        },
        getTreeCheckedKeys(name) {
            //获取tree组件的ref
            let ref = this.$refs.tree;
            let checkedKeys = ref.getCheckedKeys();
            let halfCheckedKeys = ref.getHalfCheckedKeys();
            //合并checkedKeys和halfCheckedKeys
            checkedKeys = checkedKeys.concat(halfCheckedKeys);
            console.log('ok', checkedKeys, halfCheckedKeys);
            this.formValue = checkedKeys;
        }
    },
    template: /*html*/`
    <el-form-item :label="item.label" :prop="item.prop" :rules="item.rules">
        <template v-if="!item.type || item.type=='input'">
            <el-input v-model="formValue" autocomplete="off"
                :maxlength="item.maxlength||''" :minlength="item.minlength||''"
            >
                <template v-if="item.prepend" #prepend>{{item.prepend}}</template>
                <template v-if="item.append" #append>{{item.append}}</template>
            </el-input>
        </template>
        <template v-else-if="item.type=='textarea'">
            <el-input type="textarea" v-model="formValue" autocomplete="off"></el-input>
        </template>
        <template v-else-if="item.type=='cascader'">
            <el-cascader :options="item.options" :props="item.props||{}" v-model="formValue" 
                :clearable="item.clearable||false"></el-cascader>
        </template>
        <template v-else-if="item.type=='select'">
            <el-select v-model="formValue" placeholder="请选择" :clearable="item.clearable">
                <el-option v-for="option in item.options" :key="option.value" :label="option.label" 
                    :value="option.value"></el-option>
            </el-select>
        </template>
        <template v-else-if="item.type=='switch'">
            <el-switch v-model="formValue" active-color="#13ce66" inactive-color="#ff4949"></el-switch>
        </template>
        <template v-else-if="item.type=='radio'">
            <el-radio-group v-model="formValue">
                <el-radio v-for="option in item.options" :key="option.value" 
                    :label="option.value">{{option.label}}</el-radio>
            </el-radio-group>
        </template>
        <template v-else-if="item.type=='checkbox'">
            <el-checkbox-group v-model="formValue">
                <el-checkbox v-for="option in item.options" :key="option.value"
                    :label="option.value">{{option.label}}</el-checkbox>
            </el-checkbox-group>
        </template>
        <template v-else-if="item.type=='time'">
            <el-time-picker v-model="formValue" type="time" placeholder="选择时间"></el-time-picker>
        </template>
        <template v-else-if="item.type=='timerange'">
            <el-time-picker v-model="formValue" is-range placeholder="选择时间范围"></el-time-picker>
        </template>
        <template v-else-if="item.type=='year'">
            <el-date-picker v-model="formValue" type="year" placeholder="选择年份"></el-date-picker>
        </template>
        <template v-else-if="item.type=='month'">
            <el-date-picker v-model="formValue" type="month" placeholder="选择月份"></el-date-picker>
        </template>
        <template v-else-if="item.type=='week'">
            <el-date-picker v-model="formValue" type="week" placeholder="选择周"></el-date-picker>
        </template>
        <template v-else-if="item.type=='dates'">
            <el-date-picker v-model="formValue" type="dates" placeholder="选择多个日期"></el-date-picker>
        </template>
        <template v-else-if="item.type=='monthrange'">
            <el-date-picker v-model="formValue" type="monthrange" range-separator="至"
                start-placeholder="开始月份" end-placeholder="结束月份" align="right"></el-date-picker>
        </template>
        <template v-else-if="item.type=='date'">
            <el-date-picker v-model="formValue" type="date" placeholder="选择日期"></el-date-picker>
        </template>
        <template v-else-if="item.type=='datetime'">
            <el-date-picker v-model="formValue" type="datetime" 
            value-format="YYYY-MM-DD HH:mm:ss" placeholder="选择日期时间"></el-date-picker>
        </template>
        <template v-else-if="item.type=='daterange'">
            <el-date-picker v-model="formValue" type="daterange" range-separator="至"
                start-placeholder="开始日期" end-placeholder="结束日期" align="right"
                value-format="YYYY-MM-DD"></el-date-picker>
        </template>
        <template v-else-if="item.type=='datetimerange'">
            <el-date-picker v-model="formValue" type="datetimerange" range-separator="至"
                start-placeholder="开始日期" end-placeholder="结束日期" align="right"
                value-format="YYYY-MM-DD HH:mm:ss"></el-date-picker>
        </template>
        <template v-else-if="item.type=='password'">
            <el-input type="password" v-model="formValue" autocomplete="off" 
                :placeholder="item.placeholder"></el-input>
        </template>
        <template v-else-if="item.type=='number'">
            <el-input-number v-model="formValue" :min="item.min" :max="item.max"
                :step="item.step"></el-input-number>
        </template>
        <template v-else-if="item.type=='rate'">
            <el-rate v-model="formValue" :max="item.max" :allow-half="item.allowHalf"
                :show-text="item.showText" :show-score="item.showScore"></el-rate>
        </template>
        <template v-else-if="item.type=='color'">
            <el-color-picker v-model="formValue"></el-color-picker>
        </template>
        <template v-else-if="item.type=='slider'">
            <el-slider v-model="formValue" :min="item.min" :max="item.max" :step="item.step"
                :show-input="item.showInput"></el-slider>
        </template>

        <template v-else-if="item.type.substring(0,6)==='upload'">
            <el-upload ref="upload" v-model:file-list="fileList"
                :action="item.action" :list-type="item.list_type||'text'"
                :limit="item.limit" :accept="item.accept||'*/*'" :headers="headers||{}"
                :data="{extra:item.prop}" :multiple="item.limit > 1" :show-file-list="true"
                :before-upload="beforeUpload" :on-exceed="uploadExceed" :on-remove="uploadRemove"
                :on-success="uploadSuccess" :on-error="uploadError" :on-preview="uploadPreview" 
            >
                <el-icon v-if="item.type==='upload:image'" class="picture-uploader-icon"><Plus /></el-icon>
                <el-button type="primary" v-else>上传文件</el-button>
            </el-upload>
            <el-dialog v-model="previewImageDialogVisible" v-if="item.type==='upload:image'">
                <img style="max-width:100%;" :src="previewImageDialogImageUrl" alt="图片预览" />
            </el-dialog>
        </template>
        <template v-else-if="item.type=='keyvalue'">
            <mf-key-value v-model="formValue"></mf-key-value>
        </template>
        <template v-else-if="item.type=='tree'">
            <el-tree ref="tree" show-checkbox :data="item.data" :props="item.defaultProps" 
                @check="getTreeCheckedKeys(item.prop,$event,$nodes)" :node-key="item.nodeKey" 
                :default-checked-keys="formValue===''?[]:formValue"></el-tree>
        </template>
        <template v-else-if="item.type=='icon'">
            <mf-icon-selector v-model="formValue"></mf-icon-selector>
        </template>
        <template v-else-if="item.type=='xselect'">
            <mf-xselect v-model="formValue" :item="item"></mf-xselect>
        </template>
        <div class="form-item-helper" v-if="item.intro && item.intro.length>0">
            {{item.intro}}
        </div>
    </el-form-item>
    `
}