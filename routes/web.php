<?php

use \Weigather\WJUcenterLoginService\Http\Controllers\Api\AdminController;
use \Weigather\WJUcenterLoginService\Http\Controllers\Admin\LoginController;
use \Weigather\WJUcenterLoginService\Http\Controllers\Admin\AdminUserController;


// 后台的用户列表路由重置
// Route::get('auth/users', AdminUserController::class . '@index');
Route::resource('auth/users', AdminUserController::class);


// 重写登陆逻辑
Route::get('auth/login',  LoginController::class . '@getLogin');
Route::post('auth/login',  LoginController::class . '@postLogin');

// 总码登陆逻辑
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
