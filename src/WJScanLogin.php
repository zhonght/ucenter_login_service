<?php

namespace Encore\WJScanLogin;

use Encore\Admin\Extension;

class WJScanLogin extends Extension
{
    public $name = 'wj_scan_login';

    public $views = __DIR__.'/../resources/views';

    public $assets = __DIR__.'/../resources/assets';

    public $menu = [
        'title' => '扫码登陆',
        'path'  => 'wj_scan_login',
        'icon'  => 'fa-gears',
    ];
}
