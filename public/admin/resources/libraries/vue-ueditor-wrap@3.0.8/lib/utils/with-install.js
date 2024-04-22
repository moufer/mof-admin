"use strict";

exports.__esModule = true;
exports.withInstall = withInstall;

var _camelize = require("./camelize");

function withInstall(options) {
  options.install = function (app) {
    var _ref = options,
        name = _ref.name;
    app.component(name, options);
    app.component((0, _camelize.camelize)("-" + name), options);
  };

  return options;
}