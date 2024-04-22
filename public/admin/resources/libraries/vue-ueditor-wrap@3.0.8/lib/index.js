"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

exports.__esModule = true;
exports.install = install;
exports.default = exports.version = void 0;

var _vueUeditorWrap = _interopRequireDefault(require("./vue-ueditor-wrap"));

exports.VueUeditorWrap = _vueUeditorWrap.default;
var version = '3.0.8';
exports.version = version;

function install(app) {
  var components = [_vueUeditorWrap.default];
  components.forEach(function (item) {
    if (item.install) {
      app.use(item);
    } else if (item.name) {
      app.component(item.name, item);
    }
  });
}

var _default = {
  install: install,
  version: version
};
exports.default = _default;