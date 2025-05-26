import moment from "moment";
import * as echarts from "echarts";
export default {
  components: {},
  inject: ["http", "miniapp", "apiRoot"],
  data() {
    return {
      style: null,
      items: [
        {
          prop: "session_cnt",
          label: "打开次数",
          unit: "次",
          visible: true,
        },
        {
          prop: "visit_pv",
          label: "访问次数",
          unit: "次",
          visible: true,
        },
        {
          prop: "visit_uv",
          label: "访问人数",
          unit: "个",
          visible: true,
        },
        {
          prop: "visit_uv_new",
          label: "新用户数",
          unit: "个",
          visible: true,
        },
        {
          prop: "stay_time_uv",
          label: "人均停留时长",
          unit: "秒",
          visible: false,
        },
        {
          prop: "stay_time_session",
          label: "次均停留时长",
          unit: "秒",
          visible: false,
        },
        {
          prop: "visit_depth",
          label: "平均访问深度",
          visible: false,
        },
      ],
      //trend: {}, //昨日概况
      trendLoading: false,
      rangeDate: [],
      rangeDataShortcuts: [],
      rangeTrends: [], //范围概况
      rangeTrendsLoading: false,
      activeTrend: "session_cnt",
      chart: null,
    };
  },
  created() {
    console.log("statistics.created", this.apiRoot);
    //近1周
    this.rangeDate[0] = moment().subtract(7, "days").format("YYYY-MM-DD");
    this.rangeDate[1] = moment().subtract(1, "days").format("YYYY-MM-DD");
  },

  mounted() {
    this.chart = echarts.init(this.$refs.chart);
    this.getTrends(this.rangeDate)
      .then(() => {
        this.setChartData(this.activeTrend);
      })
      .catch(() => {});
  },
  computed: {
    trend() {
      return this.rangeTrends[this.rangeTrends.length - 1] ?? [];
    },
  },
  watch: {
    rangeDate(val, oldVal) {
      console.log("rangeDate", val, oldVal);
    },
  },
  methods: {
    getTrends(rangeData) {
      let url = `/${this.apiRoot}/statistics`;
      let params = {
        begin_date: rangeData[0],
        end_date: rangeData[1],
      }; //url参数
      this.rangeTrendsLoading = true;
      //url 合并参数 params
      url += "?" + new URLSearchParams(params).toString();
      return new Promise((resolve, reject) => {
        this.http
          .get(url)
          .then((res) => {
            this.rangeTrendsLoading = false;
            this.rangeTrends = res.data;
            resolve();
          })
          .catch((err) => {
            console.log(err);
            this.rangeTrendsLoading = false;
            this.$message.error(err.errmsg);
            reject();
          })
          .finally(() => (this.rangeTrendsLoading = false));
      });
    },
    setChartData(name) {
      const item = this.items.find((item) => item.prop === name);
      const options = {
        title: {
          text: item.label,
          subtext: item.unit ? `单位：${item.unit}` : "",
        },
        tooltip: {
          trigger: "item",
        },
        xAxis: {
          type: "category",
          data: this.rangeTrends.map((item) => item.def_date),
        },
        yAxis: {
          type: "value",
        },
        series: [
          {
            name: item.label,
            type: "line",
            data: this.rangeTrends.map((item) => item[name]),
          },
        ],
      };
      this.chart.setOption(options);
    },
    handleTabClick(event) {
      this.setChartData(event.props.name);
    },
  },
  template: /*html*/ `<div class="page-statistics">
    <el-card shadow="hover" v-loading="trendLoading">
        <template #header>
            <span>昨日概况</span>
        </template>
        <el-row>
            <template v-for="item in items" :key="item.prop">
                <el-col :span="6" v-if="item.visible">
                    <el-statistic :title="item.label" :value="trend[item.prop]" style="text-align: center" />
                </el-col>
            </template>
        </el-row>
    </el-card>
    <el-card shadow="hover" v-loading="rangeTrendsLoading" style="margin-top:20px;">
    <template #header>
        <span>近一周统计</span>
    </template>
    <el-tabs v-model="activeTrend" @tab-click="handleTabClick">
        <el-tab-pane v-for="item in items" :label="item.label" :name="item.prop"></el-tab-pane>
        <div id="chart" ref="chart" style="width:100%;height:400px;"></div>
    </el-tabs>
</el-card>
</div>`,
};
