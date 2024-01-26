<?php

declare(strict_types=1);

use module\miniapp\middleware\PermissionMiddleware;
use think\facade\Route;
use module\miniapp\middleware\MiniappMiddleware;
use app\middleware\AuthTokenMiddleware;


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
    Route::group('<id>', function () {
        Route::get('statistics$', '\module\miniapp\controller\backend\Statistics@index');
        Route::get('entrance$', '\module\miniapp\controller\backend\Entrance@index');
        Route::get('package/form$', '\module\miniapp\controller\backend\Package@form');
        Route::post('package/submit$', '\module\miniapp\controller\backend\Package@submit');
        Route::get('package/download$', '\module\miniapp\controller\backend\Package@download');
        Route::post('package/downloaded$', '\module\miniapp\controller\backend\Package@downloaded');
    })->pattern(['id' => '\d+'])
        ->middleware([MiniappMiddleware::class, PermissionMiddleware::class]);

})->middleware([
    AuthTokenMiddleware::class
]);

//前端接口
Route::group('<id>', function () {

    Route::group('wechat', function () {
        //小程序登录
        Route::get('user/login', '\module\miniapp\controller\frontend\WechatUser@login');
    

    //API消息推送
    Route::get('wechat/message', '\module\miniapp\controller\frontend\Message@index');

})->middleware(MiniappMiddleware::class);





