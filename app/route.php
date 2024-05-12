<?php

use app\middleware\AuthTokenMiddleware;
use app\middleware\PermissionMiddleware;
use think\facade\Route;

Route::group('client', function () {
    Route::get('config', '\app\controller\Client@config');
});

//用户登录
Route::post('passport/login', '\app\controller\Passport@login');
Route::group('passport', function () {
    Route::post('logout', '\app\controller\Passport@logout');
    Route::get('perms', '\app\controller\Passport@perms');
    Route::get('token', '\app\controller\Passport@token');
    Route::get('info', '\app\controller\Passport@info');
})->middleware(AuthTokenMiddleware::class);

// 表前端配置
Route::get('table/<module>:<name>', '\app\controller\Table@config')
    ->middleware(AuthTokenMiddleware::class);

//Ueditor配置
Route::rule('ueditor', '\app\controller\Ueditor@index', 'GET|POST');

// 上传
Route::group('upload', function () {
    Route::post('file', '\app\controller\Upload@file');
    Route::post('image', '\app\controller\Upload@image');
    Route::post('media', '\app\controller\Upload@media');
})->middleware(AuthTokenMiddleware::class);

//需要权限验证的
Route::group(function () {

    //用户管理
    Route::resource('user', '\app\controller\User');

    // 配置
    Route::get('config/<module>', '\app\controller\Config@options');
    Route::post('config/<module>', '\app\controller\Config@submit');

    // 附件
    Route::get('storage/selector$', '\app\controller\Storage@selector');
    Route::resource('storage', '\app\controller\Storage')
        ->except(['save']);

    // 角色权限
    Route::rule('role/permission', '\app\controller\Role@permission', 'GET|POST');
    Route::resource('role', '\app\controller\Role');

    Route::resource('perm', '\app\controller\Perm');

    // 模块管理
    Route::group('module', function () {
        Route::post('install/<name>', '\app\controller\Module@install');
        Route::post('uninstall/<name>', '\app\controller\Module@uninstall');
        Route::post('enable/<name>', '\app\controller\Module@enable');
        Route::post('disable/<name>', '\app\controller\Module@disable');
        Route::post('upgrade/<name>', '\app\controller\Module@upgrade');
        Route::get('', '\app\controller\Module@index');
    });

})->middleware([
    AuthTokenMiddleware::class, // 登录校验
    PermissionMiddleware::class // 权限校验
]);