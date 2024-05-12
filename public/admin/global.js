window.clientUrl = window.location.href.replace(/\/[^/]*([?#].*)?$/, '');
//去掉clientUrl里的最后一级目录
window.serverUrl = window.clientUrl.replace(/\/[^/]*([?#].*)?$/, '');
//开发环境下
if (window.clientUrl === 'http://127.0.0.1:5500') {
    window.serverUrl = 'http://127.0.0.1:8000';
}