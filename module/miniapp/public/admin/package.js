import MfFormRender from 'comp/mf-form-render.js';
export default {
    components: {
        MfFormRender
    },
    inject: ['http', 'miniapp', 'apiRoot'],
    data() {
        return {
            active: 0,
            formRules: {},
            formValues: {},
            formItems: [],
            loading: false,
            error: '',
            position: 'right'
        }
    },
    created() {
        let url = this.apiRoot + '/package/form';
        this.http.get(url).then(res => {
            this.formItems = res.data;
            this.setFormDefaultValues(res.data);
        }).catch(err => {
            //this.$message.error(err);
            this.error = err.errmsg;
        });
    },
    mounted() {
        window.addEventListener('resize', () => {
            if (window.innerWidth < 768 && this.position !== 'top') {
                this.position = 'top';
            } else if (window.innerWidth >= 768 && this.position !== 'right') {
                this.position = 'right';
            }
        });
    },
    methods: {
        setFormDefaultValues(items) {
            items.forEach(item => {
                this.formValues[item.prop] = item.value || '';
            });
        },
        downloadPackage(key, fileName) {
            const token = localStorage.getItem('admin_token');
            const xhr = new XMLHttpRequest();
            const url = `${window.serverUrl}/${this.apiRoot}/package/download?key=${key}`;

            xhr.open('GET', url, true);
            xhr.setRequestHeader('Authorization', `Bearer ${token}`);
            xhr.responseType = 'blob';

            xhr.onload = () => {
                if (xhr.status === 200) {
                    const blob = xhr.response;
                    if (blob.type == 'application/json') {
                        const reader = new FileReader();
                        reader.readAsText(blob, 'utf-8');
                        reader.onload = () => {
                            const json = JSON.parse(reader.result);
                            this.$message.error(json.errmsg);
                        }
                    } else if (blob.type !== 'application/octet-stream') {
                        this.$message.success('下载失败');
                    } else {
                        const downloadUrl = URL.createObjectURL(blob);
                        const link = document.createElement('a');

                        link.href = downloadUrl;
                        link.download = fileName;
                        link.style.display = 'none';

                        document.body.appendChild(link);
                        link.click();

                        document.body.removeChild(link);
                        URL.revokeObjectURL(downloadUrl);

                        this.http.post(this.apiRoot + '/package/downloaded', { key });
                        this.$message.success('打包完成');
                    }
                } else {
                    this.$message.error('文件下载失败:'.xhr.status);
                }
            };

            xhr.onerror = () => {
                this.$message.error('文件下载失败');
            }

            xhr.send();
        },
        handSubmit() {
            this.$refs.formEl.validate((valid, fields) => {
                if (!valid) {
                    this.$message.error('表单验证失败');
                    return;
                }
                //提交
                this.loading = true;
                let url = this.apiRoot + '/package/submit';
                this.http.post(url, this.formValues).then(res => {
                    this.loading = false;
                    this.downloadPackage(res.data.key, res.data.filename);
                }).catch(err => {
                    console.log(err);
                    this.$message.error(err.errmsg || '打包失败');
                    this.loading = false;
                });
            });
        }
    },
    template: /*html*/`<div class="page-package">
    <el-card shadow="hover">
        <template #header>配置小程序</template>
        <el-result icon="error" title="错误提示" :sub-title="error" v-if="error" />
        <el-form ref="formEl" label-width="150px" :label-position="position"
            :model="formValues" v-if="formItems.length>0">
            <el-row>
                <el-col :xs="24" :sm="22" :md="20" :lg="19" :xl="17">
                    <mf-form-render v-for="item in formItems" :item="item" :key="item.prop"
                        v-model="formValues[item.prop]"></mf-form-render>
                    <el-form-item>
                        <el-button type="primary" @click="handSubmit" :loading="loading">打包下载</el-button>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
    </el-card>
    <el-card shadow="hover" style="margin-top: 20px;">
        <template #header>小程序上传</template>
        小程序打包下载并解压后，导入到微信小程序开发者工具，然后点击“上传”按钮即可。
    </el-card>
</div>`
}