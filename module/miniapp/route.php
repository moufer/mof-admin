<?php

declare(strict_types=1);

use module\miniapp\middleware\PermissionMiddleware;
use think\facade\Route;
use module\miniapp\middleware\MiniappMiddleware;
use app\middleware\AuthTokenMiddleware;

//API消息推送
Route::get('message/<id>', '\module\miniapp\controller\Message@index');

Route::group(function () {

    //小程序平台管理(系统后台)
    Route::resource('manage', '\module\miniapp\controller\Manage')
        ->except(['multi', 'deletes'])
        ->middleware([\app\middleware\PermissionMiddleware::class]);

    //管理员管理(系统后台)
    Route::resource('admin', '\module\miniapp\controller\Admin')
        ->except(['multi', 'deletes'])
        ->middleware([\app\middleware\PermissionMiddleware::class]);

    //小程序列表（独立后台）
    Route::group('index', function () {
        Route::get('', '\module\miniapp\controller\Index@index');
        Route::get('<id>$', '\module\miniapp\controller\Index@read');
    })->middleware(PermissionMiddleware::class);

    //小程序应用（独立后台）
    Route::group('<id>', function () {
        Route::get('statistics$', '\module\miniapp\controller\Statistics@index');
        Route::get('entrance$', '\module\miniapp\controller\Entrance@index');
        Route::get('package/form$', '\module\miniapp\controller\Package@form');
        Route::post('package/submit$', '\module\miniapp\controller\Package@submit');
        Route::get('package/download$', '\module\miniapp\controller\Package@download');
        Route::post('package/downloaded$', '\module\miniapp\controller\Package@downloaded');
    })->pattern(['id' => '\d+'])
        ->middleware([MiniappMiddleware::class, PermissionMiddleware::class]);

})->middleware([
    AuthTokenMiddleware::class
]);





