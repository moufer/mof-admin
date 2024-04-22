<?php

declare(strict_types=1);

use module\miniapp\middleware\PermissionMiddleware;
use think\facade\Route;
use module\miniapp\middleware\MiniappMiddleware;
use app\middleware\AuthTokenMiddleware;

//公共接口
Route::group('common', function () {
    Route::group('utils', function () {
        Route::get('transferImg', '\module\miniapp\controller\frontend\common\Utils@transferImg');
    });
});

//后端接口
Route::group('backend', function () {

    //小程序平台管理(系统后台)
    Route::resource('manage', '\module\miniapp\controller\backend\Manage')
        ->except(['multi', 'deletes'])
        ->middleware([\app\middleware\PermissionMiddleware::class]);

    //管理员管理(系统后台)
    Route::resource('admin', '\module\miniapp\controller\backend\Admin')
        ->except(['multi', 'deletes'])
        ->middleware([\app\middleware\PermissionMiddleware::class]);

    //小程序列表（独立后台）
    Route::group('index', function () {
        Route::get('', '\module\miniapp\controller\backend\Index@index');
        Route::get('<id>$', '\module\miniapp\controller\backend\Index@read');
    })->middleware(PermissionMiddleware::class);

    //小程序应用（独立后台）
    Route::group('<miniappId>', function () {
        Route::get('config/<module>', '\module\miniapp\controller\backend\Config@options');
        Route::post('config/<module>', '\module\miniapp\controller\backend\Config@submit');
        Route::get('statistics$', '\module\miniapp\controller\backend\Statistics@index');
        Route::get('entrance$', '\module\miniapp\controller\backend\Entrance@index');
        Route::get('pay/form$', '\module\miniapp\controller\backend\Pay@form');
        Route::post('pay/submit$', '\module\miniapp\controller\backend\Pay@submit');
        Route::get('package/form$', '\module\miniapp\controller\backend\Package@form');
        Route::post('package/submit$', '\module\miniapp\controller\backend\Package@submit');
        Route::get('package/download$', '\module\miniapp\controller\backend\Package@download');
        Route::post('package/downloaded$', '\module\miniapp\controller\backend\Package@downloaded');
    })->pattern(['miniappId' => '\d+'])
        ->middleware([MiniappMiddleware::class, PermissionMiddleware::class]);

})->middleware([
    AuthTokenMiddleware::class
]);

//前端接口
Route::group('<miniappId>', function () {

    //获取应用参数
    Route::get('config', '\module\miniapp\controller\frontend\Config@index');

    Route::group('wechat', function () {
        //API消息推送
        Route::get('message/index', '\module\miniapp\controller\frontend\Message@index');
        //小程序登录
        Route::get('user/login', '\module\miniapp\controller\frontend\WechatUser@login');
    });

})->middleware(MiniappMiddleware::class);


