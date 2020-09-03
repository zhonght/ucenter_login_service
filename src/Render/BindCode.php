<?php

namespace Encore\WJScanLogin\Render;

use Encore\WJScanLogin\Models\AdminScanLog;
use Illuminate\Contracts\Support\Renderable;

class BindCode implements Renderable
{

    public function render($key = null)
    {
        $userModel = config('admin.database.users_model');
        $adminUser = $userModel::query()->find($key);
        return view('wj_scan_login::bind',compact('key','adminUser'));
    }

}
