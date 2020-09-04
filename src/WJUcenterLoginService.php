<?php

namespace Encore\WJUcenterLoginService;

use Encore\Admin\Extension;

class WJUcenterLoginService extends Extension
{
    public $name = 'wj_ucenter_login_service';

    public $views = __DIR__.'/../resources/views';

    public $assets = __DIR__.'/../resources/assets';

    public $menu = [
        'title' => '扫码登陆',
        'path'  => 'wj_ucenter_login_service',
        'icon'  => 'fa-gears',
    ];
}
