<?php

return [

    // 是否开启扫码登陆
    'scan_enable' => env('WJ_UCENTER_LOGIN_SERVICE_ENABLE',true),

    // 是否开启扫码验证
    'verify_enable' => env('WJ_UCENTER_LOGIN_SERVICE_VERIFY_ENABLE',true),

    // 登陆的背景图
    'login_bg_img' => '',

    // 统一用户中心的配置
    'user_center' => [
        'app_host'=>env('WJ_UCENTER_LOGIN_SERVICE_APP_HOST','app_host'),
        'app_id'=>env('WJ_UCENTER_LOGIN_SERVICE_APP_ID','app_id'),
        'app_secret'=>env('WJ_UCENTER_LOGIN_SERVICE_APP_SECRET','app_secret'),
    ],

    // 官方模板 default enterprise generous technology
    'template' => env('WJ_UCENTER_LOGIN_SERVICE_TEMPLATE','default'),

    // 自定义登陆页面的视图
    'external_template' => env('WJ_UCENTER_LOGIN_SERVICE_EXTERNAL_TEMPLATE',null),

    // 是否开启验证操作密码
    'verify_operate_psw' => env('WJ_UCENTER_LOGIN_SERVICE_VERIFY_OPERATE_PSW',false),

    // 初始操作密码
    'verify_operate_psw_default' => env('WJ_UCENTER_LOGIN_SERVICE_VERIFY_OPERATE_PSW_DEFAULT','admin!123456'),

    // 自定义验证操作密码的模型
    'verify_operate_psw_model' => \Weigather\WJUcenterLoginService\Models\AdminScanConfig::class,

    // 是否开启登录通知
    'broadcast_enable' => env('WJ_UCENTER_LOGIN_SERVICE_BROADCAST_ENABLE',false),

    //登录通知配置
    'broadcast' => [
        'app_key' => env('WJ_UCENTER_LOGIN_SERVICE_BROADCAST_APP_KEY',null),
        'app_secret' => env('WJ_UCENTER_LOGIN_SERVICE_BROADCAST_APP_SECRET',null),
        'app_url' => env('WJ_UCENTER_LOGIN_SERVICE_BROADCAST_APP_URL',null),
    ],
];
