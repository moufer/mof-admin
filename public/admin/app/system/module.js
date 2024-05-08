import MfDataManage from '../../components/mf-data-manage.js';
export default {
    components: {
        MfDataManage
    },
    inject: ['http'],
    data() {
        return {
            operations: {
                label: '操作',
                show: true,
                width: 150,
                mode: 'text',
                buttons: [
                    {
                        label: '启用',
                        name: 'enable',
                        visible: "status=0",
                        click: row => this.enable(row.name)
                    }, {
                        label: '停用',
                        name: 'disable',
                        theme: 'warning',
                        visible: "status=1",
                        click: row => this.disable(row.name)
                    }, {
                        label: '安装',
                        name: 'install',
                        visible: "status=-1",
                        click: row => this.install(row.name, row)
                    }, {
                        label: '卸载',
                        name: 'uninstall',
                        visible: "status>-1",
                        theme: 'danger',
                        confirm: { title: '确定要卸载吗?' },
                        click: row => this.uninstall(row.name, row)
                    }
                ]
            }
        }
    },
    methods: {
        enable(name) {
            this.http.post(`/system/module/enable/${name}`).then(() => {
                this.$refs.manageRef.refresh();
                this.$message.success('启用成功');
                this.change();
            }).catch((err) => this.$message.error(err.errmsg || '启用失败'));
        },
        disable(name) {
            this.$confirm('确定禁用该模块吗？', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.http.post(`/system/module/disable/${name}`).then(res => {
                    this.$refs.manageRef.refresh();
                    this.$message.success('禁用成功');
                    this.change();
                }).catch((err) => this.$message.error(err.errmsg || '禁用失败'));
            });
        },
        install(name, module) {
            this.$confirm(`您确定安装【${module.title}】模块吗？`, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                //显示loading
                this.$loading({
                    lock: true,
                    text: '正在安装，请稍候...',
                });
                this.http.post(`/system/module/install/${name}`).then(() => {
                    this.$loading().close();
                    this.$refs.manageRef.refresh();
                    this.$message.success('安装成功');
                    this.change();
                }).catch((err) => {
                    this.$loading().close();
                    this.$message.error(err.errmsg || '安装失败');
                });
            });
        },
        uninstall(name, module) {
            this.$prompt(`您确定卸载<span style="color:red">${module.title}</span>模块吗？
            <br>卸载操作将删除模块相关数据库和权限信息，且<b>无法还原</b>。
            <br><br>请输入<b style="color:red;padding:5px;">ok</b>并点击确定进行删除：`, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                dangerouslyUseHTMLString: true,
                inputPattern: /^ok$/i,
                inputErrorMessage: '验证失败',
                type: 'warning'
            }).then((value) => {
                this.http.post(`/system/module/uninstall/${name}`).then(() => {
                    this.$refs.manageRef.refresh();
                    this.$message.success('卸载成功');
                    this.change();
                }).catch((err) => this.$message.error(err.errmsg || '卸载失败'));
            });
        },
        change() {
            //向主框架发送重新获取权限的消息
            this.$emit('message', 'reload_user_info');
        }
    },
    template: /*html*/`<div class="page-module">
    <mf-data-manage ref="manageRef" table-name="system:module" :table-column-operations="operations"></mf-data-manage>
</div>`,
}