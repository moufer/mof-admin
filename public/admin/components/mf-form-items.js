import MfKeyValue from './mf-key-value.js';

export default {
    name: 'mf-form-items',
    components: {
        MfKeyValue
    },
    inject: ['http'],
    emits: ['update:values'],
    props: {
        items: {
            type: Object,
            required: true
        },
        values: {
            type: Object,
            default: () => {
                return {}
            }
        },
        uploadAction: {
            type: String,
            default: ''
        },
        labelWidth: {
            type: String,
            default: '160px'
        },
        labelPosition: {
            type: String,
            default: 'right'
        },
        treeDefaultProps: {
            children: 'children',
            label: 'label',
        }
    },
    data() {
        return {
            form: {},
            previewImageDialogImageUrl: '',
            previewImageDialogVisible: false,
            iconsDrawer: false,
            icons: [],
            iconProp: '',
            uploadimage: {}
        }
    },
    created() {
        this.initData();
    },
    computed: {
        formValues: {
            get: function () {
                return this.values
            },
            set: function (newValue) {
                this.$emit('update:values', newValue)
            }
        }
    },
    methods: {
        getData() {
            return this.form;
        },
        initData() {
            this.items.forEach(item => {
                if (typeof this.values[item.prop] === 'undefined') {
                    if (['uploadimage', 'uploadfile'].indexOf(item.type) > -1) {
                        this.formValues[item.prop] = item.value;
                    } else if (['keyvalue'].indexOf(item.type) > -1) {
                        this.formValues[item.prop] = item.value ? item.value : [];
                    } else if (['switch'].indexOf(item.type) > -1) {
                        this.formValues[item.prop] = item.value ? item.value : false;
                    } else {
                        this.formValues[item.prop] = item.value ? item.value : '';
                    }
                }
                if (item.type === 'xselect') {
                    this.getXselectOptions(item);
                }
            });
        },
        uploadimageSuccess(res, file, fileList) {
            console.log('uploadimageSuccess', res, file, fileList);
            if (res.code === 200) {
                //this.formData[res.data.prop] = fileList;
            }
        },
        uploadimagePreview(file) {
            this.previewImageDialogImageUrl = file.url;
            this.previewImageDialogVisible = true;
        },
        uploadfileSuccess(res, file, fileList) {
            console.log('uploadfileSuccess', res, file, fileList);
            if (res.code === 200) {
                //this.formData[res.data.prop] = fileList;
            }
        },
        uploadfileExceed(files, fileList, e) {
            console.log(files, fileList, e);
            ElMessage.warning(`超出了上传数量`);
        },
        // 获取下拉选项
        getXselectOptions(options) {
            this.http.get(options.url, options.params || {}).then(res => {
                if (res.data.errcode === 0) {
                    options.options = res.data.data.data.map(item => {
                        return {
                            label: item['name'],
                            value: item['id']
                        };
                    });
                }
            });
        },
        getTreeCheckedKeys(name) {
            //获取tree组件的ref
            let ref = this.$refs[name][0];
            let checkedKeys = ref.getCheckedKeys();
            //let halfCheckedKeys = ref.getHalfCheckedKeys();
            this.formValues[name] = checkedKeys;
        },
        openIconsDrawer(name) {
            if (this.icons.length === 0) {
                //从elementplus里icon获取图标列表
                for (const [key,] of Object.entries(ElementPlusIconsVue)) {
                    this.icons.push(key);
                }
            }
            this.iconProp = name;
            this.iconsDrawer = true;
        },
        closeIconsDrawer(icon) {
            if (icon) {
                this.formValues[this.iconProp] = icon;
            }
            this.iconsDrawer = false;
        }
    },
    template: /*html*/`
    <el-form-item :label="item.label" prop="item.prop" v-for="item in items">
        <template v-if="!item.type || item.type=='input'">
            <el-input v-model="formValues[item.prop]" autocomplete="off"></el-input>
        </template>
        <template v-else-if="item.type=='textarea'">
            <el-input type="textarea" v-model="formValues[item.prop]" autocomplete="off"></el-input>
        </template>
        <template v-else-if="item.type=='cascader'">
            <el-cascader :options="item.options" :props="item.props||{}" v-model="formValues[item.prop]" 
            :clearable="item.clearable||false"
            ></el-cascader>
        </template>
        <template v-else-if="item.type=='select'">
            <el-select v-model="formValues[item.prop]" placeholder="请选择" :clearable="item.clearable">
                <el-option v-for="option in item.options" :key="option.value" :label="option.label" 
                    :value="option.value"></el-option>
            </el-select>
        </template>
        <template v-else-if="item.type=='xselect'">
            <el-select v-model="formValues[item.prop]" placeholder="请选择" :clearable="item.clearable">
                <el-option v-for="option in item.options" :key="option.value" :label="option.label" 
                    :value="option.value"></el-option>
            </el-select>
        </template>
        <template v-else-if="item.type=='switch'">
            <el-switch v-model="formValues[item.prop]" active-color="#13ce66" inactive-color="#ff4949"></el-switch>
        </template>
        <template v-else-if="item.type=='radio'">
            <el-radio-group v-model="formValues[item.prop]">
                <el-radio v-for="option in item.options" :key="option.value" 
                    :label="option.value">{{option.label}}</el-radio>
            </el-radio-group>
        </template>
        <template v-else-if="item.type=='checkbox'">
            <el-checkbox-group v-model="formValues[item.prop]">
                <el-checkbox v-for="option in item.options" :key="option.value"
                    :label="option.value">{{option.label}}</el-checkbox>
            </el-checkbox-group>
        </template>
        <template v-else-if="item.type=='icon'">
        <el-col :span="9">
            <el-input v-model="formValues[item.prop]" autocomplete="off" />
        </el-col>
        <el-col :span="1" :offset="1" style="text-align: center;"> 
            <el-button icon="Search" @click="openIconsDrawer(item.prop)">选择</el-button>
        </el-col>
        </template>
        <template v-else-if="item.type=='time'">
            <el-time-picker v-model="formValues[item.prop]" type="time" placeholder="选择时间"></el-time-picker>
        </template>
        <template v-else-if="item.type=='timerange'">
            <el-time-picker v-model="formValues[item.prop]" is-range placeholder="选择时间范围"></el-time-picker>
        </template>
        <template v-else-if="item.type=='year'">
            <el-date-picker v-model="formValues[item.prop]" type="year" placeholder="选择年份"></el-date-picker>
        </template>
        <template v-else-if="item.type=='month'">
            <el-date-picker v-model="formValues[item.prop]" type="month" placeholder="选择月份"></el-date-picker>
        </template>
        <template v-else-if="item.type=='week'">
            <el-date-picker v-model="formValues[item.prop]" type="week" placeholder="选择周"></el-date-picker>
        </template>
        <template v-else-if="item.type=='dates'">
            <el-date-picker v-model="formValues[item.prop]" type="dates" placeholder="选择多个日期"></el-date-picker>
        </template>
        <template v-else-if="item.type=='monthrange'">
            <el-date-picker v-model="formValues[item.prop]" type="monthrange" range-separator="至"
                start-placeholder="开始月份" end-placeholder="结束月份" align="right"></el-date-picker>
        </template>
        <template v-else-if="item.type=='date'">
            <el-date-picker v-model="formValues[item.prop]" type="date" placeholder="选择日期"></el-date-picker>
        </template>
        <template v-else-if="item.type=='datetime'">
            <el-date-picker v-model="formValues[item.prop]" type="datetime" 
            value-format="YYYY-MM-DD HH:mm:ss" placeholder="选择日期时间"></el-date-picker>
        </template>
        <template v-else-if="item.type=='daterange'">
            <el-date-picker v-model="formValues[item.prop]" type="daterange" range-separator="至"
                start-placeholder="开始日期" end-placeholder="结束日期" align="right"
                value-format="YYYY-MM-DD"></el-date-picker>
        </template>
        <template v-else-if="item.type=='datetimerange'">
            <el-date-picker v-model="formValues[item.prop]" type="datetimerange" range-separator="至"
                start-placeholder="开始日期" end-placeholder="结束日期" align="right"
                value-format="YYYY-MM-DD HH:mm:ss"></el-date-picker>
        </template>
        <template v-else-if="item.type=='password'">
            <el-input type="password" v-model="formValues[item.prop]" autocomplete="off" 
                :placeholder="item.placeholder"></el-input>
        </template>
        <template v-else-if="item.type=='number'">
            <el-input-number v-model="formValues[item.prop]" :min="item.min" :max="item.max"
                :step="item.step"></el-input-number>
        </template>
        <template v-else-if="item.type=='rate'">
            <el-rate v-model="formValues[item.prop]" :max="item.max" :allow-half="item.allowHalf"
                :show-text="item.showText" :show-score="item.showScore"></el-rate>
        </template>
        <template v-else-if="item.type=='color'">
            <el-color-picker v-model="formValues[item.prop]"></el-color-picker>
        </template>
        <template v-else-if="item.type=='slider'">
            <el-slider v-model="formValues[item.prop]" :min="item.min" :max="item.max" :step="item.step"
                :show-input="item.showInput"></el-slider>
        </template>
        <template v-else-if="item.type=='uploadfile' || item.type=='uploadfiles'">
            <el-upload :multiple="item.type==='uploadfiles'" :accept="item.accept||'*'" 
                :action="item.action" :data="{extra:item.prop}" :show-file-list="true" 
                :limit="item.type==='uploadfiles'?9:1" v-model:file-list="formValues[item.prop]"
                @on-exceed="uploadfileExceed" @on-success="uploadfileSuccess"
                :headers="item.headers||{}"
            >
                <el-button type="primary">上传</el-button>
            </el-upload>
        </template>
        <template v-else-if="item.type=='uploadimage' || item.type=='uploadimages'">
            <el-upload class="picture-uploader" :action="item.action"
                :multiple="item.type==='uploadimages'" :accept="item.accept||'image/png, image/jpeg'"
                list-type="picture-card" :data="{extra:item.prop}" :show-file-list="item.type==='uploadimages'"
                v-model:file-list="formValues[item.prop]" @on-success="uploadimageSuccess"
                @on-preview="uploadimagePreview" :headers="item.headers||{}"
            >
                <img v-if="item.type==='uploadimage' && formValues[item.prop] && formValues[item.prop].length > 0"
                    :src="formValues[item.prop][formValues[item.prop].length-1].url" 
                    class="picture-uploader-img"
                />
                <el-icon v-else class="picture-uploader-icon"><Plus /></el-icon>
            </el-upload>
            <el-dialog v-model="previewImageDialogVisible">
                <img style="max-width:100%;" :src="previewImageDialogImageUrl" alt="图片预览" />
            </el-dialog>
        </template>
        <template v-else-if="item.type=='keyvalue'">
            <mf-key-value v-model="formValues[item.prop]"></mf-key-value>
        </template>
        <template v-else-if="item.type=='tree'">
            <el-tree :ref="item.prop" show-checkbox :data="item.data" :props="item.defaultProps" 
                @check="getTreeCheckedKeys(item.prop,$event,$nodes)" :node-key="item.nodeKey" 
                :default-checked-keys="formValues[item.prop]"
            ></el-tree>
        </template>
        <div class="form-item-helper" v-if="item.intro && item.intro.length>0">{{item.intro}}</div>
    </el-form-item>
    <el-drawer
        v-model="iconsDrawer"
        title="图标选择"
        direction="rtl"
    >
        <el-row :gutter="10">
            <el-col :span="4" v-for="icon in icons" :key="icon" style="text-align: center;">
                <el-button link @click="closeIconsDrawer(icon)" style="font-size: 20px;">
                    <el-icon><component :is="icon"></component></el-icon>
                </el-button>
            </el-col>
        </el-row>

    </el-drawer>
    `
}