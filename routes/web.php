<?php

use Encore\WJScanLogin\Http\Controllers\AdminUserController;

Route::get('auth/users', AdminUserController::class . '@index');


// 重写登陆逻辑
Route::get('auth/login',  \Encore\WJScanLogin\Http\Controllers\LoginController::class . '@getLogin');
Route::post('auth/login',  \Encore\WJScanLogin\Http\Controllers\LoginController::class . '@postLogin');

Route::group([
    'prefix' => 'api'
], function ($route) {
    $route->group([
        'prefix' => 'scan'
    ], function ($route) {
        $route->post('get_bind', \Encore\WJScanLogin\Http\Controllers\ApiController::class . '@getBindCode');
    });
});
