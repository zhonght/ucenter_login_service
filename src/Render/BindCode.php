<?php

namespace Weigather\WJUcenterLoginService\Render;

use Illuminate\Contracts\Support\Renderable;

class BindCode implements Renderable
{

    public function render($key = null)
    {
        $userModel = config('admin.database.users_model');
        $adminUser = $userModel::query()->find($key);
        return view('wj_ucenter_login_service::bind',compact('key','adminUser'));
    }

}
