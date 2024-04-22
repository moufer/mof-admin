"use strict";

exports.__esModule = true;
exports.debounce = debounce;

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