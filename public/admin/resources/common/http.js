import axios from 'lib/axios@1.5.1/axios.ems.js';
import { ElMessage } from 'element-plus';
import { useRouter } from 'vue-router';

const http = axios.create({
    baseURL: window.serverUrl,
});
http.interceptors.request.use(function (cfg) {
    // 判断是否存在token，如果存在将每个页面header都添加token
    if (localStorage.getItem('admin_token')) {
        cfg.headers.Authorization = 'Bearer ' + localStorage.getItem('admin_token');
    }
    //application/json; charset=utf-8
    cfg.headers['Content-Type'] = 'application/json; charset=utf-8';
    return cfg;
}, function (err) {
    return Promise.reject(err);
});
http.interceptors.response.use(function (res) {
    if (res.data.errcode === 0) {
        return res.data; //成功
    } else {
        return Promise.reject(res.data); //错误信息
    }
}, function (err) {
    console.log('http', err.response);
    let errMsg = '';
    if (err.response.data && err.response.data.errmsg) {
        errMsg = err.response.data.errmsg;
    }
    // 对响应错误做点什么
    if (err.response.status === 401) {
        localStorage.removeItem('admin_token');
        if ('/login' !== useRouter().currentRoute.value.path) {
            ElMessage.error(errMsg ? errMsg : '登录已过期，请重新登录');
            console.log('currentRoute', http.router.currentRoute.value.path)
            setTimeout(() => {
                const router = useRouter();
                console.log('useRouter()', router);
                router.push('/login');
                //window.location.href = window.clientUrl + '/login.html';//跳转到登录框
            }, 1000)
        }
    } else if (err.response.status === 403) {
        ElMessage.error(errMsg ? errMsg : '权限不足');
    } else if (err.response.status === 404) {
        ElMessage.error(errMsg ? errMsg : '请求不存在');
    } else if (err.response.status !== 200) {
        ElMessage.error(errMsg ? errMsg : '服务器错误');
    }
    console.error(err.message, err.response);
    return Promise.reject(err);
});

export default http;