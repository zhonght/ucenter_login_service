<?php

return [

    // 是否开启扫码登陆
    'scan_enable' => env('wj_scan_enable',true),

    // 统一用户中心的配置
    'user_center' => [
        'app_id'=>env('wj_scan_app_id'),
        'app_secret'=>env('wj_scan_app_secret'),
    ],

];
