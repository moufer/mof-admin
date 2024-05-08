import { computed, reactive, watch, ref, onMounted, onUnmounted } from 'vue';
import { ElMessage } from 'element-plus';
import * as wangEditor from '../resources/libraries/wangeditor@5.1.23/editor/dist/index.esm.js';
import { isEmpty, evaluateExpression, addCss, serverUrl } from 'comm/utils.js';
export default {
    props: {
        config: {
            type: Object,
            default: () => {
                return {};
            }
        },
        modelValue: {
            type: [Object, Array, String, Number, Boolean],
            default: ''
        },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const styleEl = addCss('../resources/libraries/wangeditor@5.1.23/editor/dist/css/style.css');
        const toolbarRef = ref(null);
        const editorRef = ref(null);

        onUnmounted(() => {
            //移除样式
            styleEl?.remove();
        });

        onMounted(() => {

            const editorConfig = props.config.editorConfig || {};
            // 工具栏配置
            const toolbarConfig = props.config.toolbarConfig || {};

            editorConfig.onChange = (editor) => {
                // 当编辑器选区、内容变化时，即触发
                //console.log('content', editor.children)
                emit('update:modelValue', editor.getHtml());
            }
            const uploadCfg = {
                fieldName: 'file',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                },
                // 单个文件上传失败
                onFailed(file, res) {  // TS 语法
                    // onFailed(file, res) {          // JS 语法
                    console.log(res);
                    ElMessage.error(`${file.name} 上传失败`);
                },

                // 上传错误，或者触发 timeout 超时
                onError(file, err, res) {  // TS 语法
                    // onError(file, err, res) {               // JS 语法
                    console.log(err, res);
                    ElMessage.error(err);
                },
            }
            //配置上传
            editorConfig.MENU_CONF = {};
            //图片上传
            editorConfig.MENU_CONF['uploadImage'] = {
                server: serverUrl('/system/upload/image'),
                ...uploadCfg,
                customInsert: (res, insertFn) => {
                    if (res.errcode === 0) {
                        insertFn(res.data.url, res.data.title, '')
                    } else {
                        ElMessage.error(res.errMsg);
                    }
                },
            };
            //视频上传
            editorConfig.MENU_CONF['uploadVideo'] = {
                server: serverUrl('/system/upload/media'),
                ...uploadCfg,
                customInsert: (res, insertFn) => {
                    if (res.errcode === 0) {
                        insertFn(res.data.url, '')
                    } else {
                        ElMessage.error(res.errMsg);
                    }
                },
            };

            // 创建编辑器
            const editor = wangEditor.createEditor({
                selector: editorRef.value,
                config: editorConfig,
                mode: editorConfig.mode || 'default'
            })
            editor.setHtml(props.modelValue);
            // 创建工具栏
            const toolbar = wangEditor.createToolbar({
                editor,
                selector: toolbarRef.value,
                config: toolbarConfig,
                mode: editorConfig.mode || 'default'
            });
        });
        return {
            editorRef, toolbarRef
        };
    },
    template:/*html*/`
    <div style="z-index:10;">
        <div id="toolbar-container" ref="toolbarRef" style="border:1px solid #ccc"></div>
        <div id="editor-container" ref="editorRef" style="height:300px; overflow-y: 
            hidden;border:1px solid #ccc;border-top:0;"></div>
    </div>
    `
}