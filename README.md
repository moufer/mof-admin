MofAdmin后台管理系统
===============

## 主要新特性

* 本项目采用前后端分离，支持前后端分离开发模式
* 后端基于PHP8.1+ThinkPHP8开发
* 前端基于Vue3+ElementPlus**非构建模式**开发

## 安装

### 环境要求
* PHP 8.1+ 
* MySQL 5.7+
* Redis 5.0+
* Nginx/Apache
* Composer 2.0+

### 准备工作
* 在服务器上安装PHP、MySQL、Redis、Nginx、Composer
* 配置好MySQL和Redis
* 配置好Nginx
* 配置好Composer

### 下载代码
~~~
git clone https://gitee.com/moufer/mof-admin.git
~~~

### 安装依赖包
进入项目根目录，执行
~~~
composer install
~~~

### 配置数据库
~~~
1、复制 .env.sample 命名为 .env
2、配置 .env 文件中的MySQL和redis的链接信息
~~~

### 安装系统
进入项目根目录，执行
~~~
php think mof:install
~~~
进入命令行提示完成安装并新建管理员账号。

### 配置域名
配置好Nginx的配置文件，将项目根目录下的public目录作为网站根目录。

### 访问系统
后台是使用VUE非构建模式开发，所以不需要重新构建，直接访问即可。
访问链接 http://你的域名/admin/index.html，使用管理员账号登录。

## 开发文档
待完善
## 使用文档
待完善
## 版权信息

MofAdmin遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2024 by MouferStudio (http://www.modoer.cn)

All rights reserved。
