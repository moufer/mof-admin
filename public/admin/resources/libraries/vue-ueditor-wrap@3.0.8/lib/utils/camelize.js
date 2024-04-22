"use strict";

exports.__esModule = true;
exports.camelize = camelize;

function camelize(str) {
  return str.replace(/-(\w)/g, function (_, c) {
    return c ? c.toUpperCase() : '';
  });
}