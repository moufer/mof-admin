import * as echarts from "../../resources/libraries/echarts@5.5.1/dist/echarts.esm.min.js";

export default {
  name: "mf-page-dashboard",
  inject: ["http"],
  components: {},
  mounted() {
    this.getTotalData().then(() => {
      setTimeout(() => {
        this.setCharts();
      }, 500);
    });
  },
  methods: {
    getTotalData: function () {
      let url = `/system/dashboard`;
      return new Promise((resolve, reject) => {
        this.http
          .get(url)
          .then((res) => {
            this.rangeTrendsLoading = false;
            this.totals = res.data.totals;
            this.charts = res.data.charts;
            resolve();
          })
          .catch((err) => {
            console.log(err);
            this.$message.error(err.errmsg);
            reject();
          })
          .finally(() => (this.rangeTrendsLoading = false));
      });
    },
    setCharts: function () {
      this.charts.forEach((option, index) => {
        let chartDom = document.getElementById("chart" + index);
        let myChart = echarts.init(chartDom);
        myChart.setOption(option);
        console.log("xxxx", option);
      });
    },
  },
  data() {
    return {
      rangeTrendsLoading: false,
      totals: [],
      charts: [],
    };
  },

  template: /*html*/ `
    <div class="page-dashboard">
      <el-card shadow="hover">
        <div slot="header" style="display:flex;align-items: center;">
          <Histogram style="width: 1.2em; height: 1.2em; margin-right: 5px"/>
          <span>数据统计</span>
        </div>
        <el-row>
          <template v-for="(total, index) in totals" :key="index">
            <el-col :span="6" style="text-align: center;margin-top:20px;">
              <el-statistic :value="total.today">
                <template #title>今日{{total.title}}</template>
              </el-statistic>
              <div v-if="total.total>-1">
                <span style="color: #999999;font-size:12px;">
                总计 {{total.total}}
                </span>
              </div>
            </el-col>
          </template>
        </el-row>
      </el-card>

      <div class="chart-container">
        <el-card shadow="hover" v-for="(chart, index) in charts" :key="index" style="margin-top:20px;">
          <div slot="header" style="display:flex;align-items: center;">
            <TrendCharts style="width: 1.2em; height: 1.2em; margin-right: 5px"/>
            <span>{{chart.extra.title}}</span>
          </div>
          <div style="height:250px;width:100%;margin:0 auto;" :id="'chart'+index"></div>
        </el-card>
      </div>
    </div>
    
    <div style="margin-top:20px;font-size:12px;text-align:center;color:var(--el-text-color-secondary);">
      Copyright (c) 2019-2024 陌风软件开发工作室
    </div>
    `,
};
