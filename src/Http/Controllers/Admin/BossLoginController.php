<?php

namespace Weigatherboss\BossLogin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;


use Encore\Admin\Layout\Content;
use Illuminate\Routing\Controller;

class BossLoginController extends Content
{
    /**
     * 认证器
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    public function bossLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'boss' => 'nullable'
        ]);
        if (isset($request->boss)) {
            
            if ($validator->fails()) {
                return wj_boss_login_service_api('500', [], '验证失败');
            }
            $adminUser = decrypt($request->boss);
            // 判断总码是否被登陆过 获取过期
            if(Redis::exists('boss_'.$adminUser['username']) == false){
                return wj_boss_login_service_api('500', [], 'Token已过期');
            }
            $boss = Redis::get('boss_'.$adminUser['username']);
            if($boss != $request->boss){
                return wj_boss_login_service_api('500', [], 'Token无效');
            }

            if ($this->guard()->loginUsingId($adminUser['admin_id'])) {
                Redis::del('boss_'.$adminUser['username']);
                return $this->sendLoginResponse($request);
            }
            return wj_boss_login_service_api('500', [], 'Token无效');
        }else{
            return wj_boss_login_service_api('500', [], 'Token无效');
        }
    }

    /**
     * 登陆成的跳转
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));
        if (get_wj_boss_login_service_version() >= 1) {
            $request->session()->regenerate();
        }
        return redirect()->intended($this->redirectPath());
    }

    /**
     * 获取跳转地址
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }
        return property_exists($this, 'redirectTo') ? $this->redirectTo : get_wj_boss_login_service_version() >= 1?config('admin.route.prefix'):config('admin.prefix');
    }
}