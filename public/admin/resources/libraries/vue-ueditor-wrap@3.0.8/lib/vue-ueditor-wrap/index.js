"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

exports.__esModule = true;
exports.VueUeditorWrap = exports.default = void 0;

var _utils = require("../utils");

var _VueUeditorWrap2 = _interopRequireDefault(require("./VueUeditorWrap"));

var VueUeditorWrap = (0, _utils.withInstall)(_VueUeditorWrap2.default);
exports.VueUeditorWrap = VueUeditorWrap;
var _default = VueUeditorWrap;
exports.default = _default;