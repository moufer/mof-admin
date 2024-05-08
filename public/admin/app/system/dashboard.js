export default {
  name: 'mf-page-dashboard',
  components: {
  },
  created() {
  },

  template: /*html*/`
    <div class="page-dashboard">
    <el-row>
    <el-col :span="6">
      <el-statistic title="Daily active users" :value="0" />
    </el-col>
    <el-col :span="6">
      <el-statistic :value="0">
        <template #title>
          <div style="display: inline-flex; align-items: center">
            Ratio of men to women
          </div>
        </template>
        <template #suffix>/0</template>
      </el-statistic>
    </el-col>
    <el-col :span="6">
      <el-statistic title="Total Transactions" :value="0" />
    </el-col>
    <el-col :span="6">
      <el-statistic title="Feedback number" :value="0"></el-statistic>
    </el-col>
  </el-row>
    </div>`,
}