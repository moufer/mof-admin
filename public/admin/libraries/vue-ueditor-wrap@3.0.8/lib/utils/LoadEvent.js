"use strict";

exports.__esModule = true;
exports.LoadEvent = void 0;

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

exports.LoadEvent = LoadEvent;