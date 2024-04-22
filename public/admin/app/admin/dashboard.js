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
      <el-statistic title="Daily active users" :value="268500" />
    </el-col>
    <el-col :span="6">
      <el-statistic :value="138">
        <template #title>
          <div style="display: inline-flex; align-items: center">
            Ratio of men to women
          </div>
        </template>
        <template #suffix>/100</template>
      </el-statistic>
    </el-col>
    <el-col :span="6">
      <el-statistic title="Total Transactions" :value="100" />
    </el-col>
    <el-col :span="6">
      <el-statistic title="Feedback number" :value="562"></el-statistic>
    </el-col>
  </el-row>
    </div>`,
}