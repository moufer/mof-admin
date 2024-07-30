import MfDataManage from 'comp/mf-data-manage.js';
export default {
    components: {
        MfDataManage
    },
    data() {
        return {
            formatter: {
                size: row => row.size + '字节'
            }
        }
    },
    template: /*html*/`
    <div class="system-storage">
        <mf-data-manage ref="manageRef" table-name="system:storage" :table-column-formatter="formatter" />
    </div>
`,
}