<?php

use \Weigather\WJUcenterLoginService\Http\Controllers\Api\AdminController;
use \Weigather\WJUcenterLoginService\Http\Controllers\Admin\LoginController;
use \Weigather\WJUcenterLoginService\Http\Controllers\Admin\AdminUserController;


// 后台的用户列表路由重置
Route::get('auth/users', AdminUserController::class . '@index');


// 重写登陆逻辑
Route::get('auth/login',  LoginController::class . '@getLogin');
Route::post('auth/login',  LoginController::class . '@postLogin');


Route::get('wj_scan/list/{adminId}', AdminUserController::class . '@scanList');
Route::get('wj_scan/bind/{adminId}', AdminUserController::class . '@scanBind');

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
