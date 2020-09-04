<?php

namespace Encore\WJUcenterLoginService\Http\Controllers\Admin;

use Encore\WJUcenterLoginService\Models\AdminScanBind;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }
        return view('wj_ucenter_login_service::index');
    }

    public function postLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required_without:admin_token',
            'password' => 'required_without:admin_token',
            'admin_token' => 'nullable'
        ]);
        if (isset($request->admin_token)) {
            if ($validator->fails()) {
                return back()->withInput()->withErrors($validator);
            }
            $adminUser = decrypt($request->admin_token);
            if ($this->guard()->loginUsingId($adminUser['id'])) {
                return $this->sendLoginResponse($request);
            }
            return back()->withInput()->withErrors([
                'username' => $this->getFailedLoginMessage(),
            ]);
        } else {
            if ($validator->fails()) {
                return wj_ucenter_login_service_return('500', [], $validator->errors()->first());
            }
            $credentials = $request->only(['username', 'password']);
            if ($this->guard()->validate($credentials)) {
                $userModel = config('admin.database.users_model');
                $adminModel = $userModel::query()->where('username', $request->username)->first();

                if (config('wj_ucenter_login_service.verify_enable')) {
                    return wj_ucenter_login_service_return('403', [
                        'username' => $adminModel->username,
                        'name' => $adminModel->name,
                        'token' => encrypt(['type' => 'create_qr_code', 'id' => $adminModel->id, 'time' => time()]),
                        'is_verify' => AdminScanBind::where('admin_id', $adminModel->id)->count() > 0,
                    ]);
                }else{
                    if ($this->guard()->loginUsingId($adminModel->id)) {
                        admin_toastr(trans('admin.login_successful'));
                        $request->session()->regenerate();
                        return wj_ucenter_login_service_return('00', [$this->redirectPath()],'登陆成功');
                    }
                }
            }
            return wj_ucenter_login_service_return('500', [], '账号密码错误');
        }
    }

    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));
        $request->session()->regenerate();
        return redirect()->intended($this->redirectPath());
    }


    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }
        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }


    protected function guard()
    {
        return Auth::guard('admin');
    }
}
