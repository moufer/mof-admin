(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("vue"));
	else if(typeof define === 'function' && define.amd)
		define("vue-ueditor-wrap", ["vue"], factory);
	else if(typeof exports === 'object')
		exports["vue-ueditor-wrap"] = factory(require("vue"));
	else
		root["vue-ueditor-wrap"] = factory(root["Vue"]);
})(typeof self !== 'undefined' ? self : this, function(__WEBPACK_EXTERNAL_MODULE__197__) {
return /******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 197:
/***/ (function(module) {

module.exports = __WEBPACK_EXTERNAL_MODULE__197__;

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "VueUeditorWrap": function() { return /* reexport */ vue_ueditor_wrap; },
  "default": function() { return /* binding */ es; },
  "install": function() { return /* binding */ install; },
  "version": function() { return /* binding */ version; }
});

;// CONCATENATED MODULE: ./es/utils/camelize.js
function camelize(str) {
  return str.replace(/-(\w)/g, function (_, c) {
    return c ? c.toUpperCase() : '';
  });
}
;// CONCATENATED MODULE: ./es/utils/with-install.js

function withInstall(options) {
  options.install = function (app) {
    var _ref = options,
        name = _ref.name;
    app.component(name, options);
    app.component(camelize("-" + name), options);
  };

  return options;
}
// EXTERNAL MODULE: external {"root":"Vue","commonjs":"vue","commonjs2":"vue","amd":"vue"}
var external_root_Vue_commonjs_vue_commonjs2_vue_amd_vue_ = __webpack_require__(197);
;// CONCATENATED MODULE: ./es/utils/LoadEvent.js
// 一个简单的事件订阅发布的实现
var LoadEvent = /*#__PURE__*/function () {
  function LoadEvent() {
    this.listeners = {};
  }

  var _proto = LoadEvent.prototype;

  _proto.on = function on(eventName, callback) {
    if (this.listeners[eventName] === undefined) {
      this.listeners[eventName] = {
        triggered: false,
        requested: false,
        cbs: []
      };
    } // 如果已经触发过，后续添加监听的 callback 会被直接执行


    if (this.listeners[eventName].triggered) {
      callback();
    }

    this.listeners[eventName].cbs.push(callback);
  };

  _proto.emit = function emit(eventName) {
    if (this.listeners[eventName]) {
      this.listeners[eventName].triggered = true;
      this.listeners[eventName].cbs.forEach(function (callback) {
        return callback();
      });
    }
  };

  return LoadEvent;
}();
;// CONCATENATED MODULE: ./es/utils/async-series.js
/**
 * 串行执行异步任务的函数，函数参数是一个数组，数组的每一项都是一个函数，这些函数会返回 promise，前一个函数 promise resolve 的值会作为下一个函数的入参
 * @param funs
 * @returns
 */
function asyncSeries(funs) {
  return funs.reduce(function (promise, fun) {
    return promise.then(fun);
  }, Promise.resolve());
}
;// CONCATENATED MODULE: ./es/utils/debounce.js
/**
 * 一个简单的防抖函数
 * @param func 需要限制执行频率的函数
 * @param delay 延迟时间，这段时间过后，才可触发第二次
 * @returns void
 */
function debounce(func, delay) {
  var timer;

  var debouncedFunction = function debouncedFunction() {
    var _this = this;

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    if (timer) clearTimeout(timer);
    timer = setTimeout(function () {
      func.apply(_this, args);
    }, delay);
  };

  debouncedFunction.cancel = function () {
    if (timer !== undefined) {
      clearTimeout(timer);
    }
  };

  return debouncedFunction;
}
;// CONCATENATED MODULE: ./es/utils/randomString.js
/**
 * 生成指定长度的随机字符串
 * @param {number} length 字符串长度
 * @returns 随机字符串
 */
function randomString(length) {
  var alphabet = 'abcdefghijklmnopqrstuvwxyz';
  var str = '';

  for (var i = 0; i < length; i++) {
    str += alphabet.charAt(Math.floor(Math.random() * alphabet.length));
  }

  return str;
}
;// CONCATENATED MODULE: ./es/vue-ueditor-wrap/VueUeditorWrap.js



/* harmony default export */ var VueUeditorWrap = ((0,external_root_Vue_commonjs_vue_commonjs2_vue_amd_vue_.defineComponent)({
  name: 'vue-ueditor-wrap',
  props: {
    // 手动设置 UEditor ID
    editorId: String,
    // 常用于表单中 http://fex.baidu.com/ueditor/#start-submit
    name: String,
    modelValue: {
      type: String,
      default: ''
    },
    // http://fex.baidu.com/ueditor/#start-config
    config: Object,
    // 监听富文本内容变化的方式
    mode: {
      type: String,
      default: 'observer',
      validator: function validator(value) {
        // 1. observer 借助 MutationObserver API https://developer.mozilla.org/zh-CN/docs/Web/API/MutationObserver
        // 2. listener 借助 UEditor 的 contentChange 事件 https://ueditor.baidu.com/doc/#UE.Editor:contentChange
        return ['observer', 'listener'].indexOf(value) !== -1;
      }
    },
    // MutationObserver 的配置 https://developer.mozilla.org/en-US/docs/Web/API/MutationObserverInit
    observerOptions: {
      type: Object,
      default: function _default() {
        return {
          attributes: true,
          // 是否监听 DOM 元素的属性变化
          attributeFilter: ['src', 'style', 'type', 'name'],
          // 只有在该数组中的属性值的变化才会监听
          characterData: true,
          // 是否监听文本节点
          childList: true,
          // 是否监听子节点
          subtree: true // 是否监听后代元素

        };
      }
    },
    // MutationObserver 的回调函数防抖间隔
    observerDebounceTime: {
      type: Number,
      default: 50,
      validator: function validator(value) {
        return value >= 20;
      }
    },
    //  SSR 项目，服务端实例化组件时组件内部不会对 UEditor 进行初始化，仅在客户端初始化 UEditor，这个参数设置为 true 可以跳过环境检测，直接初始化
    forceInit: Boolean,
    // 是否在组建销毁时销毁 UEditor 实例
    destroy: {
      type: Boolean,
      default: true
    },
    // 指定 UEditor 依赖的静态资源，js & css
    editorDependencies: {
      type: Array
    },
    // 检测依赖的静态资源是否加载完成的方法
    editorDependenciesChecker: {
      type: Function
    }
  },
  emits: ['update:modelValue', 'before-init', 'ready'],
  setup: function setup(props, _ref) {
    var emit = _ref.emit;
    var STATUS_MAP = {
      UN_READY: 'UN_READY',
      // 尚未初始化
      PENDING: 'PENDING',
      // 开始初始化但尚未 ready
      READY: 'READY' // 初始化完成并已 ready

    };
    var status = STATUS_MAP.UN_READY;
    var editor;
    var observer;
    var innerValue;
    var container = (0,external_root_Vue_commonjs_vue_commonjs2_vue_amd_vue_.ref)(); // 默认要加载的资源

    var defaultEditorDependencies = ['ueditor.config.js', 'ueditor.all.min.js']; // 判断上面的默认资源是否已经加载过的校验函数

    var defaultEditorDependenciesChecker = function defaultEditorDependenciesChecker() {
      // 判断 ueditor.config.js 和 ueditor.all.js 是否均已加载
      // 仅加载完ueditor.config.js时UE对象和UEDITOR_CONFIG对象存在,仅加载完ueditor.all.js时UEDITOR_CONFIG对象存在,但为空对象
      return window.UE && window.UE.getEditor && window.UEDITOR_CONFIG && Object.keys(window.UEDITOR_CONFIG).length !== 0;
    };

    var modelValue = (0,external_root_Vue_commonjs_vue_commonjs2_vue_amd_vue_.toRef)(props, 'modelValue'); // 创建加载资源的事件通信载体

    if (!window.$loadEventBus) {
      window.$loadEventBus = new LoadEvent();
    } // 动态创建 script 标签来加载 JS 脚本，保证同一个脚本只被加载一次


    var loadScript = function loadScript(link) {
      return new Promise(function (resolve, reject) {
        window.$loadEventBus.on(link, resolve);

        if (window.$loadEventBus.listeners[link].requested === false) {
          window.$loadEventBus.listeners[link].requested = true; // 如果这个资源从未被请求过，就手动创建脚本去加载

          var script = document.createElement('script');
          script.src = link;

          script.onload = function () {
            window.$loadEventBus.emit(link);
          };

          script.onerror = reject;
          document.getElementsByTagName('head')[0].appendChild(script);
        }
      });
    }; // 动态创建 link 标签来加载 CSS 文件


    var loadCss = function loadCss(link) {
      return new Promise(function (resolve, reject) {
        window.$loadEventBus.on(link, resolve);

        if (window.$loadEventBus.listeners[link].requested === false) {
          window.$loadEventBus.listeners[link].requested = true;
          var css = document.createElement('link');
          css.type = 'text/css';
          css.rel = 'stylesheet';
          css.href = link;

          css.onload = function () {
            window.$loadEventBus.emit(link);
          };

          css.onerror = reject;
          document.getElementsByTagName('head')[0].appendChild(css);
        }
      });
    }; // 加载 UEditor 相关的静态资源


    var loadEditorDependencies = function loadEditorDependencies() {
      return new Promise(function (resolve, reject) {
        if (props.editorDependencies && props.editorDependenciesChecker && props.editorDependenciesChecker()) {
          resolve();
          return;
        }

        if (!props.editorDependencies && defaultEditorDependenciesChecker()) {
          resolve();
          return;
        } // 把 js 和 css 分组


        var _reduce = (props.editorDependencies || defaultEditorDependencies).reduce(function (res, link) {
          // 如果不是完整的 URL 就在前面补上 UEDITOR_HOME_URL, 完整的 URL 形如：
          // 1. http://www.example.com/xxx.js
          // 2. https://www.example.com/xxx.js
          // 3. //www.example.com/xxx.js
          // 4. www.example.com/xxx.js
          var isFullUrl = /^((https?:)?\/\/)?[-a-zA-Z0-9]+(\.[-a-zA-Z0-9]+)+\//.test(link);

          if (!isFullUrl) {
            var _props$config;

            link = (((_props$config = props.config) == null ? void 0 : _props$config.UEDITOR_HOME_URL) || '') + link;
          }

          if (link.slice(-3) === '.js') {
            res.jsLinks.push(link);
          } else if (link.slice(-4) === '.css') {
            res.cssLinks.push(link);
          }

          return res;
        }, {
          jsLinks: [],
          cssLinks: []
        }),
            jsLinks = _reduce.jsLinks,
            cssLinks = _reduce.cssLinks;

        Promise.all([Promise.all(cssLinks.map(function (link) {
          return loadCss(link);
        })), // 依次加载依赖的 JS 文件，JS 执行是有顺序要求的，比如 ueditor.all.js 就要晚于 ueditor.config.js 执行
        // 动态创建 script 是先加载完的先执行，所以不可以一次性创建所有资源的引入脚本
        asyncSeries(jsLinks.map(function (link) {
          return function () {
            return loadScript(link);
          };
        }))]).then(function () {
          return resolve();
        }).catch(reject);
      });
    }; // 基于 UEditor 的 contentChange 事件


    var observerContentChangeHandler = function observerContentChangeHandler() {
      innerValue = editor.getContent();
      emit('update:modelValue', innerValue);
    };

    var normalChangeListener = function normalChangeListener() {
      editor.addListener('contentChange', observerContentChangeHandler);
    }; // 基于 MutationObserver API


    var changeHandle = function changeHandle() {
      if (editor.document.getElementById('baidu_pastebin')) {
        return;
      }

      innerValue = editor.getContent();
      emit('update:modelValue', innerValue);
    };

    var observerChangeListener = function observerChangeListener() {
      observer = new MutationObserver(debounce(changeHandle, props.observerDebounceTime));
      observer.observe(editor.body, props.observerOptions);
    }; // 实例化编辑器


    var initEditor = function initEditor() {
      var editorId = props.editorId || 'editor_' + randomString(8);
      container.value.id = editorId;
      emit('before-init', editorId);
      editor = window.UE.getEditor(editorId, props.config);
      editor.addListener('ready', function () {
        if (status === STATUS_MAP.READY) {
          // 使用 keep-alive 组件会出现这种情况
          editor.setContent(props.modelValue);
        } else {
          status = STATUS_MAP.READY;
          emit('ready', editor);

          if (props.modelValue) {
            editor.setContent(props.modelValue);
          }
        }

        if (props.mode === 'observer' && window.MutationObserver) {
          observerChangeListener();
        } else {
          normalChangeListener();
        }
      });
    };

    (0,external_root_Vue_commonjs_vue_commonjs2_vue_amd_vue_.watch)(modelValue, function (value) {
      if (status === STATUS_MAP.UN_READY) {
        status = STATUS_MAP.PENDING;
        (props.forceInit || typeof window !== 'undefined') && loadEditorDependencies().then(function () {
          container.value ? initEditor() : (0,external_root_Vue_commonjs_vue_commonjs2_vue_amd_vue_.nextTick)(function () {
            return initEditor();
          });
        }).catch(function () {
          throw new Error('[vue-ueditor-wrap] UEditor 资源加载失败！请检查资源是否存在，UEDITOR_HOME_URL 是否配置正确！');
        });
      } else if (status === STATUS_MAP.READY) {
        value === innerValue || editor.setContent(value || '');
      }
    }, {
      immediate: true
    });
    (0,external_root_Vue_commonjs_vue_commonjs2_vue_amd_vue_.onDeactivated)(function () {
      editor && editor.removeListener('contentChange', observerContentChangeHandler);
      observer && observer.disconnect();
    });
    (0,external_root_Vue_commonjs_vue_commonjs2_vue_amd_vue_.onBeforeUnmount)(function () {
      if (observer && observer.disconnect) {
        observer.disconnect();
      }

      if (props.destroy && editor && editor.destroy) {
        editor.destroy();
      }
    });
    return function () {
      return (0,external_root_Vue_commonjs_vue_commonjs2_vue_amd_vue_.createVNode)("div", null, [(0,external_root_Vue_commonjs_vue_commonjs2_vue_amd_vue_.createVNode)("div", {
        "ref": container,
        "name": props.name
      }, null)]);
    };
  }
}));
;// CONCATENATED MODULE: ./es/vue-ueditor-wrap/index.js


var vue_ueditor_wrap_VueUeditorWrap = withInstall(VueUeditorWrap);
/* harmony default export */ var vue_ueditor_wrap = (vue_ueditor_wrap_VueUeditorWrap);

;// CONCATENATED MODULE: ./es/index.js

var version = '3.0.8';

function install(app) {
  var components = [vue_ueditor_wrap];
  components.forEach(function (item) {
    if (item.install) {
      app.use(item);
    } else if (item.name) {
      app.component(item.name, item);
    }
  });
}


/* harmony default export */ var es = ({
  install: install,
  version: version
});
}();
/******/ 	return __webpack_exports__;
/******/ })()
;
});