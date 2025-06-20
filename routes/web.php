<?php

use \Weigather\WJUcenterLoginService\Http\Controllers\Api\AdminController;
use \Weigather\WJUcenterLoginService\Http\Controllers\Admin\LoginController;
use \Weigather\WJUcenterLoginService\Http\Controllers\Admin\AdminUserController;


// 后台的用户列表路由重置
// Route::get('auth/users', AdminUserController::class . '@index');
Route::resource('auth/users', AdminUserController::class);


// 重写‌登录逻辑
Route::get('auth/login',  LoginController::class . '@getLogin');
Route::post('auth/login',  LoginController::class . '@postLogin');

// 总码‌登录逻辑
Route::get('auth/item_login', LoginController::class. '@itemLogin');

// 绑定二维码生成接口
Route::group([
    'prefix' => 'api'
], function ($route) {
    $route->group([
        'prefix' => 'scan'
    ], function ($route) {
        $route->post('get_bind',AdminController::class . '@getBindCode');
    });
});

// v1.8.1-v1.8.10的扫码绑定走这里
Route::get('wj_scan/bind/{adminId}', AdminUserController::class . '@scanBind');


// 总码‌登录逻辑
Route::any('auth/boss_login',LoginController::class. '@bossLogin');
