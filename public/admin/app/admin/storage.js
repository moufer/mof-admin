import MfDataManage from 'comp/mf-data-manage.js';
export default {
    components: {
        MfDataManage
    },
    template: /*html*/`<div class="mf-dynamic">
    <mf-data-manage ref="manageRef" table-name="admin:storage"></mf-data-manage>
</div>
    `,
}