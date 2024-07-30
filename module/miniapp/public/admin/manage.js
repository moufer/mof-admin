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
                width: 200,
                fixed:'right',
                buttons: [
                    {
                        label: '管理',
                        name: 'manage',
                        click: row => {
                            let url = `./miniapp.html?id=${row.id}#/miniapp/statistics`;
                            //打开新页面,用生成a标签实现
                            let link = document.createElement('a');
                            link.href = url;
                            link.target = '_blank';
                            document.body.appendChild(link);
                            link.click();
                            //删除link
                            document.body.removeChild(link);
                        }
                    },
                    'edit',
                    {
                        label: '删除',
                        theme: 'danger',
                        click: row => {
                            this.$prompt(`您确定要删除 <span style="color:red">${row.title}</span> 小程序平台吗？
                            <br><br><b style="color:red">删除操作将删除平台相关的所有数据，且无法还原！！</b>
                            <br><br>请输入<b style="color:red;padding:5px;">确认删除</b>并点击确定进行删除：`, '慎重提示', {
                                confirmButtonText: '确定',
                                cancelButtonText: '取消',
                                dangerouslyUseHTMLString: true,
                                inputPattern: /^确认删除$/i,
                                inputErrorMessage: '验证失败',
                                type: 'warning'
                            }).then((value) => {
                                this.http.delete(`/miniapp/backend/manage/${row.id}`).then(() => {
                                    this.$refs.manageRef.refresh();
                                    this.$message.success('删除成功');
                                }).catch((err) => {
                                    console.log('err', err);
                                    this.$message.error(err.errmsg || '删除失败');
                                });
                            });
                        }
                    }
                ]
            },
        }
    },
    created() {
        //console.log('operations', this.operations);
    },
    template: /*html*/`<div class="page-miniapp">
    <mf-data-manage ref="manageRef" 
        table-name="miniapp:miniapp" :table-column-operations="operations"></mf-data-manage>
</div>`,
}