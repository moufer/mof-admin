MofAdmin
===============

> 运行环境要求PHP8.1+ThinkPHP8.0+MySQL5.7

## 主要新特性

* 基于PHP8.1+ThinkPHP8开发
* 本项目采用前后端分离，支持前后端分离开发模式
* 后端基于PHP8.1+ThinkPHP8开发
* 前端基于Vue3+ElementPlus**非构建模式**开发

## 安装

### 下载代码
~~~
git clone https://gitee.com/moufer/mof-admin.git
~~~
### 安装依赖包
~~~
composer install
~~~
### 配置数据库
~~~
1、复制 .env.sample.php 到 .env
2、修改 .env 文件中的数据库和redis配置
~~~
### 安装系统
进入系统所在目录，执行
~~~
php think mof:install
~~~
进入命令行提示完成安装并新建管理员账号。
### 进入系统
访问 http://你的域名/admin/index.html，使用管理员账号登录。

## 开发文档

## 使用文档

## 版权信息

MofAdmin遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2024 by MouferStudio (http://www.modoer.cn)

All rights reserved。
