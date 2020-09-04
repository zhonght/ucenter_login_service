<?php

return [

    // 是否开启扫码登陆
    'scan_enable' => env('WJ_UCENTER_LOGIN_SERVICE_ENABLE',true),

    // 统一用户中心的配置
    'user_center' => [
        'app_id'=>env('WJ_UCENTER_LOGIN_SERVICE_APP_ID'),
        'app_secret'=>env('WJ_UCENTER_LOGIN_SERVICE_APP_SECRET'),
    ],

];
