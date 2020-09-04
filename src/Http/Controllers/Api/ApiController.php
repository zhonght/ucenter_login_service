<?php

namespace Encore\WJUcenterLoginService\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Encore\Admin\Auth\Database\Administrator;
use Encore\WJUcenterLoginService\Models\AdminScanBind;
use Encore\WJUcenterLoginService\Models\AdminScanLog;
use Encore\WJUcenterLoginService\SDK\ServiceUserCenter;
use Illuminate\Http\Request;

class ApiController extends Controller
{


    public function checkData(Request $request){
        $sdkModel = new ServiceUserCenter();
        if ($sdkModel->sign($request->except('sign'), $this->appSecret) != $request->sign) {
            return wj_ucenter_login_service_return('100005');
        }
        return true;
    }

    public function status(Request $request)
    {
        $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
        if ($request->type == 1) {
            $scanLog->status = 1;
        }
        $scanLog->scan_status = $request->type;
        $scanLog->save();
        return wj_ucenter_login_service_return('200',[],'操作成功');
    }

    public function bind(Request $request)
    {
        $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
        $scanLog->status = 3;
        $scanLog->user_token = $request->user_token;
        $scanLog->result = ['admin_token' => encrypt(['id' => $scanLog->data['admin_id'], 'type'=>'login_token','time' => time()])];
        $scanLog->save();
        if(AdminScanBind::where('admin_id',$scanLog->data['admin_id'])->where('user_token',$request->user_token)->count()>0){
            return wj_ucenter_login_service_return('200',[],'该账号已经绑定过,无需重复绑定');
        }
        $scanUser = new AdminScanBind();
        $scanUser->admin_id = $scanLog->data['admin_id'];
        $scanUser->user_token = $request->user_token;
        $scanUser->save();
        return wj_ucenter_login_service_return('200',[],'绑定成功');
    }

    public function login(Request $request)
    {
        $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
        $scanLog->status = 3;
        $scanLog->result = ['admin_token' => $request->token];
        $scanLog->save();
        return wj_ucenter_login_service_return('200',[],'登陆成功');
    }

    public function auth(Request $request)
    {
        $adminToken = decrypt($request->token);
        if ($adminToken && is_array($adminToken) && $adminToken['type'] == 'verify_qr_code' && $adminToken['time'] + (24 * 60 * 60) > time()) {
            $adminId = $adminToken['id'];
            if (AdminScanBind::where('admin_id', $adminId) -> where('user_token', $request->user_token) -> count() > 0) {

                if($request->is_login){
                    $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
                    $scanLog->status = 3;
                    $scanLog->user_token =  $request->user_token;
                    $scanLog->result = ['token' => $request->token,'admin_token'=>encrypt(['id' =>$adminId, 'type'=>'login_token','time' => time()])];
                    $scanLog->save();
                    return wj_ucenter_login_service_return('200',[],'确认成功');
                }

                return wj_ucenter_login_service_return('200',[],'检测成功');
            }
            return wj_ucenter_login_service_return('500',[],'您不是该账户的管理员,请检查微信号是否正确');
        }
        return wj_ucenter_login_service_return('500',[],'token非法');
    }


    public function users(Request $request)
    {
        return wj_ucenter_login_service_return('200', AdminScanBind::where('user_token', $request->user_token)->get()->map(function ($map) {
            $admin = Administrator::find($map->admin_id);
            return [
                'nickname' => $admin->name,
                'username' => $admin->username,
                'avatar' => wj_ucenter_login_service_resource_url($admin->avatar),
                'token' => encrypt(['id' => $admin->id, 'type'=>'login_token','time' => time()]),
            ];
        }));
    }




}
