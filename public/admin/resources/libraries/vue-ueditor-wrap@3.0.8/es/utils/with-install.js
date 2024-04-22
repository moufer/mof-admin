import { camelize } from './camelize';
export function withInstall(options) {
  options.install = function (app) {
    var _ref = options,
        name = _ref.name;
    app.component(name, options);
    app.component(camelize("-" + name), options);
  };

  return options;
}