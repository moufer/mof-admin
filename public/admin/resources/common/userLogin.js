import http from 'comm/http.js';

const clear = function () {
    ['admin_token', 'admin_user', 'admin_perms'].forEach(key => {
        localStorage.removeItem(key);
    });
}

const loginSuccess = function (res) {
    localStorage.setItem('admin_token', res.data.token.token);
}

const loginFail = function (err) {
    clear();
}

const logout = function (forward = '') {
    return new Promise((resolve, reject) => {
        http.post('/system/passport/logout').then(res => {
            clear();
            resolve();
        })
    })
}

const isLogin = function () {
    const token = localStorage.getItem('admin_token');
    const isLogin = sessionStorage.getItem('isLogin');
    return new Promise((resolve, reject) => {
        if (token && isLogin === 'true') {
            resolve(true);
        } else if (token) {
            http.get('/system/passport/token').then(res => {
                resolve(true);
            }).catch(err => {
                clear();
                reject(false);
            });
        } else {
            reject(false);
        }
    });

}

export { loginSuccess, loginFail, logout, isLogin }