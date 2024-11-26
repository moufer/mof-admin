<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/11/25 23:19
 */

namespace app\controller;

use mof\Request;
use think\App;

class Index
{
    public function __construct(protected App $app, protected Request $req)
    {
    }

    public function admin(): string
    {
        //返回httpdoc代码
        return
<<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>磨锋后台管理</title>
  <link
    rel="stylesheet"
    href="./resources/libraries/element-plus@2.8.5/index.css"
  />
  <link rel="stylesheet" href="./resources/css/app.css"/>
  <link rel="stylesheet" href="./resources/css/page.css"/>
  <!-- <script src="https://unpkg.com/vconsole@latest/dist/vconsole.min.js"></script> -->
  <!-- <script>new window.VConsole()</script> -->
  <script>
    window.__SITE_NAME__ = "磨锋后台系统";
    window.__LOGIN_MODULE__ = "system";
  </script>
</head>

<body>
<div id="app"></div>
<script type="importmap">
  {
    "imports": {
      "@/": "./",
      "@/app/": "./app/",
      "lib/": "./resources/libraries/",
      "comp/": "./components/",
      "comm/": "./resources/common/",

      "utils": "./resources/common/utils.js",
      "http": "./resources/common/http.js",
      "mf-components": "./components/mf-components.js",

      "vue": "./resources/libraries/vue@3.5.12/vue.esm-browser.prod.js",
      "vue-router": "./resources/libraries/vue-router@4.4.5/vue-router.esm-browser.js",
      "pinia": "./resources/libraries/pinia@2.1.7/pinia-esm.js",
      "axios":"./resources/libraries/axios@1.5.1/axios.ems.js",
      "element-plus": "./resources/libraries/element-plus@2.8.5/index.full.min.mjs",
      "element-plus-icons-vue": "./resources/libraries/element-plus-icons-vue@2.3.1/index.js",
      "moment": "./resources/libraries/moment@2.29.4/dist/moment.js",
      "lodash": "./resources/libraries/lodash/dist/lodash-esm.js",
      "wovoui-icons": "./resources/libraries/wovoui-icons@1.1.9/index.es.mjs",
      "echarts": "./resources/libraries/echarts@5.5.1/dist/echarts.esm.min.js",
      "vue-ueditor-wrap":"./resources/libraries/vue-ueditor-wrap@3.0.8/es/vue-ueditor-wrap-esm.js"
    }
  }
</script>
<script src="./config.js"></script>
<script type="module" src="./app/system/common/main.js" defer></script>
</body>
</html>
EOT;
    }
}