<?php


Route::group([
    'prefix' => 'api'
], function ($route) {
    $route->group([
        'prefix' => 'scan'
    ], function ($route) {
        $route->any('status', \Encore\WJScanLogin\Http\Controllers\ApiController::class . '@status');
        $route->any('bind', \Encore\WJScanLogin\Http\Controllers\ApiController::class . '@bind');
        $route->any('login', \Encore\WJScanLogin\Http\Controllers\ApiController::class . '@login');
        $route->any('auth', \Encore\WJScanLogin\Http\Controllers\ApiController::class . '@auth');
        $route->any('users', \Encore\WJScanLogin\Http\Controllers\ApiController::class . '@users');





        $route->post('get_login', \Encore\WJScanLogin\Http\Controllers\ApiController::class . '@getLoginCode');
        $route->post('get_verify', \Encore\WJScanLogin\Http\Controllers\ApiController::class . '@getVerifyCode');
        $route->post('code_status', \Encore\WJScanLogin\Http\Controllers\ApiController::class . '@getCodeStatus');
    });
});
