import VueUeditorWrap from './vue-ueditor-wrap';
var version = '3.0.8';

function install(app) {
  var components = [VueUeditorWrap];
  components.forEach(function (item) {
    if (item.install) {
      app.use(item);
    } else if (item.name) {
      app.component(item.name, item);
    }
  });
}

export { install, version, VueUeditorWrap };
export default {
  install: install,
  version: version
};