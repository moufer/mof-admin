import { reactive, getCurrentInstance, inject, onUnmounted, ref } from 'vue'
import { ElLoading, ElMessage } from 'element-plus';
import { addStyle } from 'comm/utils.js';
import { loginSuccess } from 'comm/userLogin.js';
import { useUserStore } from 'comm/userStore.js';
import { useRouteStore } from 'comm/routeStore.js';
import { useRouter } from 'vue-router';

export default {
  emits: ['onsuccess', 'onerror'],
  setup(props, { emit }) {

    const instance = getCurrentInstance();
    const router = useRouter();

    const http = inject('http');
    const loginTitle = inject('loginTitle', '磨锋后台管理系统');
    const loginModule = inject('loginModule', '磨锋后台管理系统');

    const formValues = reactive({ username: '', password: '', module: loginModule });
    const loginRules = {
      username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
      password: [{ required: true, message: '请输入密码', trigger: 'blur' }]
    };

    const handleLogin = () => {
      instance.refs.formRef.validate(valid => {
        if (valid) {
          // 显示 Loading 组件
          const loading = ElLoading.service({
            text: 'Loading',
            background: 'rgba(0, 0, 0, 0.7)',
          });

          // 发送登录请求
          http.post('/system/passport/login', formValues)
            .then(res => {
              //emit('onsuccess', res);
              loginSuccess(res);

              useUserStore().setUser(res.data.user);
              if (res.data.perms) {
                useRouteStore().setPerms(res.data.perms);
              }

              let pagePath = '';
              if (window.location.hash.length > 2) {
                pagePath = window.location.hash.substring(1);
              }
              if (pagePath === '/login' || !router.hasRoutePath(pagePath)) {
                pagePath = router.firstRoute().path;
              }
              router.push(pagePath);

            }).catch(err => {
              console.log(err);
              err.errmsg && ElMessage.error(err.errmsg);
            }).finally(() => loading.close());
        }
      });
    };

    //组件样式动态添加
    const styleStr = instance.type?.style;
    const styleEl = styleStr ? addStyle(styleStr) : null;

    onUnmounted(() => {
      //移除样式
      styleEl?.remove();
    });

    return {
      loginTitle, formValues, loginRules,
      handleLogin
    }
  },
  template: /*html*/`
  <div class="page-login-box">
    <div class="login-title">{{loginTitle}}</div>
    <el-form ref="formRef" class="login-form" label-width="80px" :model="formValues" :rules="loginRules">
        <el-form-item label="用户名" prop="username">
            <el-input v-model="formValues.username"></el-input>
        </el-form-item>
        <el-form-item label="密码" prop="password">
            <el-input v-model="formValues.password" type="password" autocomplete="off" 
              @keyup.enter="handleLogin"></el-input>
        </el-form-item>
        <el-form-item>
            <el-button type="primary" @click="handleLogin">登录</el-button>
        </el-form-item>
    </el-form>
    <div class="login-page-copyright">
        Copyright (c) 2019-2024 陌风软件开发工作室
    </div>
  </div>
  `,
  style: /*css*/`
  body {
    background-color: #393d4a;
  }

  .page-login-box {
    width: 400px;
    margin: 0 auto;
    margin-top: 150px;
  }

  .page-login-box .login-title {
    font-size: 24px;
    font-weight: bold;
    color: #ffffff;
    text-align: center;
    margin-bottom: 20px;
  }

  .page-login-box .login-form {
    background-color: #ffffff;
    padding: 50px 30px 30px 30px;
    border-radius: 5px;
    box-shadow: 0px 2px 10px #888888;
  }

  .page-login-box .login-form .el-form-item {
    margin-bottom: 20px;
  }

  .page-login-box .login-form .el-form-item__label {
    font-size: 16px;
    font-weight: bold;
    color: #333333;
  }

  .page-login-box .login-form .el-input {
    font-size: 16px;
    border-width: 2px;
  }

  .page-login-box .login-form .el-button {
    width: 100%;
    font-size: 16px;
    background-color: #409EFF;
    border-color: #409EFF;
    border-radius: 5px;
  }

  .page-login-box .login-page-copyright {
    font-size: 14px;
    color: #ffffff;
    text-align: center;
    margin-top: 20px;
  } 
  `
}