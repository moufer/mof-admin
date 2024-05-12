import http from 'comm/http.js';
import { useConfigStore } from 'comm/configStore.js';

const getConfig = () => {
    return new Promise((resolve, reject) => {
        http.get('/system/client/config').then(res => {
            useConfigStore().setConfig(res.data);
            resolve(res.data);
        }).catch(err => {
            reject(err);
        });
    })
}

export {
    getConfig
};