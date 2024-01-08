<?php

declare(strict_types=1);

use think\facade\Route;
use module\miniapp\middleware\MiniappMiddleware;
use app\middleware\AuthTokenMiddleware;
use app\middleware\PermissionMiddleware;

//API消息推送
Route::get('message/<id>', '\module\miniapp\controller\Message@index');

Route::group(function () {

    //小程序平台管理
    Route::resource('manage', '\module\miniapp\controller\Manage')
        ->except(['multi', 'deletes']);

    //小程序应用
    Route::group('<id>', function () {
        Route::get('statistics$', '\module\miniapp\controller\Statistics@index');
        Route::get('entrance$', '\module\miniapp\controller\Entrance@index');
        Route::get('package/form$', '\module\miniapp\controller\Package@form');
        Route::post('package/submit$', '\module\miniapp\controller\Package@submit');
        Route::get('package/download$', '\module\miniapp\controller\Package@download');
        Route::post('package/downloaded$', '\module\miniapp\controller\Package@downloaded');
    })
        ->pattern(['id' => '\d+'])
        ->middleware([MiniappMiddleware::class]);

})->middleware([
    AuthTokenMiddleware::class,
    PermissionMiddleware::class
]);





