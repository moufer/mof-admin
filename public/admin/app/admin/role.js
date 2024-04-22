import MfDataManage from '../../components/mf-data-manage.js';
export default {
    name: 'mf-page-role',
    components: {
        MfDataManage
    },
    template: /*html*/`<div class="mf-dynamic">
    <mf-data-manage ref="manageRef" table-name="admin:role"></mf-data-manage>
</div>`,
}