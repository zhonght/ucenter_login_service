<?php

use \Weigather\WJUcenterLoginService\Http\Controllers\Api\ApiController;
use \Weigather\WJUcenterLoginService\Http\Controllers\Api\AdminController;

Route::get('fuck', function(){
    return  444;
});
Route::group([
    'prefix' => 'api'
], function ($route) {
    $route->group([
        'prefix' => 'scan'
    ], function ($route) {

        // 提供给用户服务的接口
        $route->post('status', ApiController::class . '@status');
        $route->post('bind', ApiController::class . '@bind');
        $route->post('login', ApiController::class . '@login');
        $route->post('auth', ApiController::class . '@auth');
        $route->post('users',ApiController::class . '@users');

        // 自身扫码的接口
        $route->post('get_login',AdminController::class . '@getLoginCode');
        $route->post('get_verify', AdminController::class . '@getVerifyCode');
        $route->post('code_status', AdminController::class . '@getCodeStatus');
    });
});
