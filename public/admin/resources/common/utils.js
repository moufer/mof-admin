import { useConfigStore } from 'comm/configStore.js';

/**
 * 服务器端链接地址
 * @param {string} url 
 * @param {string|array} params 
 * @returns 
 */
const serverUrl = (url, params = []) => {
    //如果url开头是"/"，则去掉
    if (url.charAt(0) === '/') {
        url = url.substring(1);
    }
    if (params.length > 0) {
        url += '?' + buildQueryString(params);
    }
    return window.serverUrl + '/' + url;
}

/**
 * 客户端链接地址
 * @param {string} url 
 * @param {string|array} params 
 * @returns 
 */
const clientUrl = (url, params = []) => {
    //如果url开头是"/"，则去掉
    if (url.charAt(0) === '/') {
        url = url.substring(1);
    }
    if (params.length > 0) {
        url += '?' + buildQueryString(params);
    }
    return window.clientUrl + '/' + url;
}

/**
 * 存储端链接地址
 * @param {string} url 
 * @param {string|array} params 
 * @returns 
 */
const storageUrl = (url, provider = 'default') => {
    const { config } = useConfigStore();
    if (url.charAt(0) === '/') {
        url = url.substring(1);
    }
    console.log('config', config)
    return config.storageUrl + '/' + url;
}

/**
 * 判断给定变量是否为空。
 * 
 * @param {any} variable - 要检查的变量。
 * @returns {boolean} - 如果变量为空，则返回true；否则返回false。
 */
const isEmpty = (variable) => {
    // 检查变量是否为null或undefined
    if (variable === null || variable === undefined) {
        return true;
    }

    // 检查变量是否为字符串或数组，并且长度为0
    if (typeof variable === 'string' || Array.isArray(variable)) {
        return variable.length === 0;
    }

    // 检查变量是否为对象，并且没有属性
    if (typeof variable === 'object') {
        for (let key in variable) {
            if (variable.hasOwnProperty(key)) {
                return false;
            }
        }
        return true;
    }

    // 变量不为空
    return false;
}

/**
 * 深拷贝一个对象。
 *
 * @param {any} obj - 需要深拷贝的对象。
 * @return {any} 深拷贝后的对象。
 */
const deepCopy = (obj) => {
    // 处理特殊对象类型
    if (obj instanceof RegExp) {
        return new RegExp(obj);
    } else if (obj instanceof Date) {
        return new Date(obj.getTime());
    } else if (typeof obj === 'function') {
        return obj;
    } else if (typeof obj !== 'object' || obj === null) {
        return obj;
    }

    // 根据 obj 的类型来创建一个空的副本
    let copy;
    if (Array.isArray(obj)) {
        copy = [];
    } else {
        copy = {};
    }

    // 递归地拷贝属性
    for (let key in obj) {
        if (obj.hasOwnProperty(key)) {
            copy[key] = deepCopy(obj[key]);
        }
    }

    return copy;
}

/**
 * 根据给定的数据评估表达式。
 *
 * @param {string} exp - 要评估的表达式。
 * @param {object} data - 评估中使用的数据。
 * @return {boolean} 评估结果。
 */
const evaluateExpression = (exp, data) => {
    // 将表达式分解为单独的条件
    let conditions = exp.split(/(&&|\|\|)/);

    let result = null;
    let nextOperator = null;

    conditions.forEach(condition => {
        condition = condition.trim();
        if (condition === '&&' || condition === '||') {
            nextOperator = condition;
            return;
        }

        // 分解条件为操作符和值
        let matches = condition.match(/(.*?)(>=|<=|<|>|=)(.*)/);
        let key = matches[1].trim();
        let operator = matches[2];
        let value = matches[3].trim();

        // 根据操作符进行比较
        let conditionResult = false;
        switch (operator) {
            case '=':
                let regexFlags = '';
                if (value.substring(0, 6) === 'regex:') { // 匹配正则
                    let regexString = value.substring(6);
                    let lastIndex = regexString.lastIndexOf("/");
                    regexFlags = regexString.slice(lastIndex + 1);
                    value = regexString.slice(1, lastIndex - 1);
                    conditionResult = typeof data[key] !== 'undefined'
                        && new RegExp(value, regexFlags).test(data[key]);
                } else if (value.substring(0, 3) === 'in:') { // 匹配数组
                    value = value.substring(3);
                    //检测value里是否存在","，如果存在则转换成数组
                    value = value.indexOf(',') !== -1 ? value.split(',') : [value]
                    //循环遍历value
                    value.forEach(item => {
                        if (typeof data[key] !== 'undefined') {
                            conditionResult = conditionResult || data[key].indexOf(item) !== -1;
                        }
                    });
                } else {
                    //遍历数组，检查是不是数字字符串，如果是则转为数字
                    value = value.split(',').map(item => {
                        item = item.trim();
                        let regex = /^[\-0-9]+$/;
                        if (regex.test(item)) return Number(item);
                        if (item === 'true') return true;
                        if (item === 'false') return false;
                        return item;
                    });
                    conditionResult = typeof data[key] !== 'undefined'
                        ? value.indexOf(data[key]) !== -1
                        : false;
                }
                break;
            case '>=':
            case '<=':
            case '>':
            case '<':
                let operators = {
                    '>=': (a, b) => a >= b,
                    '<=': (a, b) => a <= b,
                    '>': (a, b) => a > b,
                    '<': (a, b) => a < b,
                };
                //如果value是“length:数字”，则获取数字
                if (value.substring(0, 7) === 'length:') {
                    value = value.substring(7);
                    conditionResult = typeof data[key] !== 'undefined'
                        ? operators[operator](data[key].length, Number(value))
                        : false;
                } else {
                    let isNumber = typeof data[key] === 'number';

                    //比较数字大小
                    conditionResult = isNumber && operators[operator](data[key], Number(value));
                }
        }

        // 根据上一个操作符应用逻辑运算
        if (result === null) {
            result = conditionResult;
        } else if (nextOperator === '&&') {
            result = result && conditionResult;
        } else if (nextOperator === '||') {
            result = result || conditionResult;
        }
    });

    return result;
}

const addStyle = (style) => {
    let styleEl = document.createElement('style');
    styleEl.setAttribute('type', 'text/css');
    styleEl.innerHTML = style;
    document.head.appendChild(styleEl);

    return {
        remove: () => {
            document.head.removeChild(styleEl);
        }
    }
}

const addCss = (url) => {
    let linkEl = document.createElement('link');
    linkEl.setAttribute('rel', 'stylesheet');
    linkEl.setAttribute('type', 'text/css');
    linkEl.setAttribute('href', url);
    document.head.appendChild(linkEl);

    return {
        remove: () => {
            document.head.removeChild(linkEl);
        }
    }
}

const removeStyle = (styleEl) => {
    document.head.removeChild(styleEl);
}

const buildQueryString = (params, parentKey = '') => {
    const queryString = Object.keys(params).map((key) => {
        const value = params[key];
        const nestedKey = parentKey ? `${parentKey}[${key}]` : key;

        if (typeof value === 'object') {
            return buildQueryString(value, nestedKey);
        }

        return `${encodeURIComponent(nestedKey)}=${encodeURIComponent(value)}`;
    }).join('&');

    return queryString;
}

//暂停1秒钟
const sleep = (time) => {
    return new Promise((resolve) => {
        setTimeout(resolve, time);
    })
}

const formDefaultValue = function (type) {
    switch (type) {
        case 'cascader':
        case 'key-value':
        case 'transfer':
        case 'upload:file':
        case 'upload:image':
        case 'checkbox':
            return [];
        case 'input-dict':
            return {};
        case 'rate':
        case 'slider':
        case 'input-number':
            return 0;
        default:
            return ''
    }
}

const getThumbByFileType = function (filePath, fileType) {
    let ext = ['video', 'audio', 'ppt', 'doc', 'zip', 'pdf'];
    let type = fileType || 'other';
    let src = clientUrl('/resources/images/file-other.png');
    if ('image' === fileType) {
        src = filePath.substring(0, 4) !== 'http' ? storageUrl(filePath) : filePath;
    } else if (ext.indexOf(fileType) > -1) {
        src = clientUrl(`/resources/images/file-${type}.png`);
    }
    return src;
}

export {
    isEmpty,
    deepCopy,
    evaluateExpression,
    addStyle,
    addCss,
    removeStyle,
    formDefaultValue,
    buildQueryString,
    sleep,
    getThumbByFileType,
    serverUrl,
    clientUrl,
    storageUrl
};