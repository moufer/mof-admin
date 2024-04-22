import { inject, ref, getCurrentInstance, onUnmounted, computed } from 'vue';
import { addStyle } from 'comm/utils.js';
import { BoxSeam, Wechat } from 'wovoui-icons';
import { ElLoading, ElMessage } from 'element-plus';
import { useUserStore } from 'comm/userStore.js';
import { logout } from 'comm/userLogin.js';

export default {
  components: {
    BoxSeam, Wechat
  },
  setup() {
    const instance = getCurrentInstance();

    const http = inject('http');
    const apps = ref([]);
    const active = ref('all');
    const { user } = useUserStore();
    const types = [
      { label: '所有平台', prop: 'all', icon: 'BoxSeam' },
      { label: '微信小程序', prop: 'wechat', icon: 'Wechat' },
    ];

    const userLogout = () => {
      logout().then(() => {
        document.location.href = '/miniapp.html';
      });
    }

    const appList = (page = 1) => {
      const url = `miniapp/backend/index?type=${active.value}&page=${page}&page_size=100`;
      http.get(url).then(res => {
        if (page === 1) {
          apps.value = res.data.data;
        } else {
          apps.value.push(...res.data.data);
        }
      }).catch(err => {
        err.errmsg && ElMessage.error(err.errmsg);
      })
    }

    const selectType = (type) => {
      active.value = type;
      appList();
    }

    const gotoManage = async (id) => {
      try {
        const data = await http.get(`/miniapp/backend/index/${id}`);
        const url = `?id=${id}#/miniapp/statistics`;
        document.location.href = url;
      } catch (error) {
        error.errmsg && ElMessage.error(error.errmsg);
      }
    }

    //组件样式动态添加
    const styleStr = instance.type?.style;
    const styleEl = styleStr ? addStyle(styleStr) : null;
    onUnmounted(() => {
      //移除样式
      styleEl?.remove();
    });

    appList();

    return {
      apps,
      user,
      active,
      types,
      gotoManage,
      selectType,
      userLogout
    }

  },
  template: /*html*/`
    <div class="page-index">
      <div class="page-title">
        <h1>磨锋小程序平台</h1>
        <div class="title-desc">
          <div class="avatar-box">
            <img class="avatar-img" :src="user.avatar" />
            <el-dropdown>
              <span class="user-name">
                {{user.name||user.username}}
                <el-icon>
                  <arrow-down />
                </el-icon>
              </span>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item>资料</el-dropdown-item>
                  <el-dropdown-item @click="userLogout">登出</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </div>
      </div>
      <div class="page-main">
        <div class="index-aside">
          <ul class="index-aside-list">
            <template v-for="type in types">
            <li :class="{'active':active == type.prop}" @click="selectType(type.prop)">
              <span class="label"><component :is="type.icon" />{{type.label}}</span>
              <span class="count"></span>
            </li>
            </template>
          </ul>
        </div>
        <div class="index-main">
          <div class="app-list">
            <div class="app-detail" v-for="app in apps" :key="app.id">
              <el-card :body-style="{padding:'0px'}" shadow="hover" @click="gotoManage(app.id)">
                <img :src="app.avatar_img" class="image"/>
                <div class="app-detail-info">
                  <span class="title">{{app.title}}</span>
                  <div class="bottom">
                    <span class="intro">{{ app.intro }}</span>
                    <span class="intro">ID:{{ app.id }}</span>
                  </div>
                </div>
              </el-card>
            </div>
          </div>
        </div>
      </div>
    </div>
    `,
  style:/*css*/`
    body {
      background-color: #fff;
    }
    .page-index {
      margin: 0;
    }
    .page-title {
      height: 50px;
      margin-bottom:10px;
      background-color: #f8f8f8;
      border-bottom: 1px solid #eee;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
    }
    .page-title h1 {
      margin: 0;
      padding: 0;
      font-size: 20px;
      color: #555;
    }
    .title-desc {
      display: flex;
      align-items: center;
    }
    .title-desc .avatar-box {
      height: 30px;
      text-align: center;
      cursor: pointer;
      display: flex;
      flex-direction: row;
      align-items: center;
    }
    .title-desc .avatar-img {
      border-radius: 50%;
      width: 30px;
      height: 30px;
      display: inline-block;
      margin-right: 5px;
    }
    .title-desc .avatar-img span {
      margin-left: 5px;
      cursor: pointer;
      color: var(--el-color-white);
      outline: none;
      display: flex;
      align-items: center;
    }
    .page-main {
      margin: 0;
      display: flex;
      flex-direction: row;
      justify-content: center;
      padding: 10px 20px;
    }

    .index-aside {
      width: 250px;
      margin-right: 20px;
      background-color: #f9f9f9;
    }
    .index-aside-list {
      list-style: none;
      padding: 0;
      margin: 0;
      min-height: 350px;
    }
    .index-aside-list li {
      padding: 10px 20px;
      cursor: pointer;
      color: #666;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .index-aside-list li span.label {
      margin-left:5px;
      display: flex;
      align-items: center;
    }
    .index-aside-list li span.label svg {
      margin-right: 5px;
    }
    .index-aside-list li span.count {
      color: #aaa;
    }
    .index-aside-list li:hover,
    .index-aside-list li.active {
      background-color: #E8F1FF;
      color: #409EFF;
    }
    .index-main {
      width: calc(100% - 300px);
    }
    .app-list {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      flex-direction: row;
      justify-content: left;
    }
    .app-detail {
      margin-bottom: 20px;
      cursor: pointer;
      padding: 0 20px;
      width: 250px;
    }
    .app-detail .image {
      width: 100%;
      height: 250px;
      display: block;
      /*图片自适应*/
      object-fit:cover;
      border-bottom: 1px solid #eee;
    }
    .app-detail-info {
      padding: 14px;
    }
    .app-detail-info .title {
      font-size: 18px;
    }
    .app-detail-info .intro {
      font-size: 14px;
      color: #999;
      /*只显示一行*/
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
      
    }
    .app-detail-info .bottom {
      margin-top: 13px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .app-detail-info .button {
      padding: 0;
      min-height: auto;
    }
    `,

}