<?php

use \Weigather\WJUcenterLoginService\Http\Controllers\Api\AdminController;
use \Weigather\WJUcenterLoginService\Http\Controllers\Admin\LoginController;
use \Weigather\WJUcenterLoginService\Http\Controllers\Admin\AdminUserLowController ;


// 后台的用户列表路由重置
// Route::get('auth/users', AdminUserLowController::class . '@index');
Route::resource('auth/users', AdminUserLowController::class);

// 重写‌登录逻辑
Route::get('auth/login',  LoginController::class . '@getLogin');
Route::post('auth/login',  LoginController::class . '@postLogin');

// 总码‌登录逻辑
Route::get('auth/item_login', LoginController::class. '@itemLogin');


Route::get('wj_scan/list/{adminId}', AdminUserLowController::class . '@scanList');
Route::get('wj_scan/bind/{adminId}', AdminUserLowController::class . '@scanBind');
Route::delete('wj_scan/list/{adminId}/{id}', AdminUserLowController::class . '@scanBindDestroy');

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
