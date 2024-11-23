MofAdmin-磨锋后台开发框架
===============

## 特性
* 本项目采用前后端分离开发模式
* 后端基于PHP8.1+ThinkPHP8
* 前端基于Vue3+ElementPlus**非构建模式**开发
* 使用模块化开发，模块热插拔
* 后台常用组件集成封装，只需编写PHP代码即可完成后台页面的设计，降低后端开发人员开发前端页面的难度，专注后端代码，提高开发效率
* 本项目为纯后台项目，适合作为WebApp，手机App、小程序等项目的后端开发

## 演示
* https://mof.modoer.cn/admin
* 账号：demo 密码：demo888

## 案例
* [磨锋AIGC系统](https://gitee.com/moufer/mof-aigc)

## 安装

### 环境要求
* PHP 8.1+ 
* MySQL 5.7+
* Redis 5.0+
* Nginx/Apache
* Composer 2.0+

### 准备工作
* 服务器上安装：*PHP、MySQL、Redis、Nginx/Apache、Composer*
* 安装PHP扩展：*redis*、*zip*、*iconv*、*fileinfo*
* 通过宝塔安装的PHP，请解禁（PHP设置-禁用函数）函数： *putenv*、*proc_open*

### 下载代码
~~~
git clone https://gitee.com/moufer/mof-admin.git
~~~

### 安装依赖包
进入项目根目录，执行（国内服务器建议把 composer 镜像设置为[阿里云源](https://developer.aliyun.com/composer)或者[腾讯源](https://mirrors.tencent.com/help/composer.html)）
~~~
composer install
~~~

### 配置数据库
~~~
1、复制 .env.sample 命名为 .env
2、配置 .env 文件中的MySQL、redis和JWT key
~~~

### 安装系统
进入项目根目录，执行命令
~~~
php think mof:install
~~~
根据安装程序提示完成安装并新建管理员账号。

### 配置域名
nginx/apache配置新域名，并将项目的public目录作为网站根目录。

### 访问系统
后台是使用VUE非构建模式开发，所以不需要重新构建，直接访问即可。
访问链接 `https://你的域名/admin/index.html`，使用管理员账号登录。

## 开发文档
待完善

## 使用文档
待完善

## 版权信息

MofAdmin遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2024 by MouferStudio (http://www.modoer.cn)

All rights reserved。
