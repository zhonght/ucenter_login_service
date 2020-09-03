<?php

namespace Encore\WJScanLogin\Http\Controllers;


use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Database\Administrator;
use Encore\WJScanLogin\Models\AdminScanBind;
use Encore\WJScanLogin\Models\AdminScanLog;
use Encore\WJScanLogin\SDK\ServiceUserCenter;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ApiController extends Controller
{



    public function checkData(Request $request){
        $sdkModel = new ServiceUserCenter();
        if ($sdkModel->sign($request->except('sign'), $this->appSecret) != $request->sign) {
            return api_return('100005');
        }
        return true;
    }

    public function status(Request $request)
    {
//        $res = $this->checkData();
//        if($res !== true){
//            return $res;
//        }
        $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
        if ($request->type == 1) {
            $scanLog->status = 1;
        }
        $scanLog->scan_status = $request->type;
        $scanLog->save();
        return api_return('200',[],'操作成功');
    }

    public function bind(Request $request)
    {
        $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
        $scanLog->status = 3;
        $scanLog->user_token = $request->user_token;
        $scanLog->result = ['admin_token' => encrypt(['id' => $scanLog->data['admin_id'], 'type'=>'login_token','time' => time()])];
        $scanLog->save();
        if(AdminScanBind::where('admin_id',$scanLog->data['admin_id'])->where('user_token',$request->user_token)->count()>0){
            return api_return('200',[],'该账号已经绑定过,无需重复绑定');
        }
        $scanUser = new AdminScanBind();
        $scanUser->admin_id = $scanLog->data['admin_id'];
        $scanUser->user_token = $request->user_token;
        $scanUser->save();
        return api_return('200',[],'绑定成功');
    }

    public function login(Request $request)
    {
        $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
        $scanLog->status = 3;
        $scanLog->result = ['admin_token' => $request->token];
        $scanLog->save();
        return api_return('200',[],'登陆成功');
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
                    return api_return('200',[],'确认成功');
                }

                return api_return('200',[],'检测成功');
            }
            return api_return('500',[],'您不是该账户的管理员,请检查微信号是否正确');
        }
        return api_return('500',[],'token非法');
    }


    public function users(Request $request)
    {
        return api_return('200', AdminScanBind::where('user_token', $request->user_token)->get()->map(function ($map) {
            $admin = Administrator::find($map->admin_id);
            return [
                'nickname' => $admin->name,
                'username' => $admin->username,
                'avatar' => resource_url($admin->avatar),
                'token' => encrypt(['id' => $admin->id, 'type'=>'login_token','time' => time()]),
            ];
        }));
    }





    public function getLoginCode(Request $request)
    {
        $res = AdminScanLog::createLoginCode();
        if ($res['code'] == '00') {
            $scanLogModel = new  AdminScanLog();
            $scanLogModel->code_id = $res['data'][0]['code_id'];
            $scanLogModel->type = 1;
            $scanLogModel->status = 0;
            $scanLogModel->expiration_time = time() + $res['data'][0]['expiry_time'];
            $scanLogModel->data = ['url' => $res['data'][0]['url']];
            $scanLogModel->save();
            return api_return($res['code'], [
                [
                    'url'=>$this->_scanQrCodeUrl($res['data'][0]['url']),
                    'code_id' => $res['data'][0]['code_id']
                ]
            ]);
        }
        return api_return($res['code'], $res['data'], $res['msg']);
    }




    public function getBindCode(Request $request)
    {
        $res = AdminScanLog::createBindCode($request->id);
        if ($res['code'] == '00') {
            $scanLogModel = new  AdminScanLog();
            $scanLogModel->code_id = $res['data'][0]['code_id'];
            $scanLogModel->type = 2;
            $scanLogModel->status = 0;
            $scanLogModel->expiration_time = time() + $res['data'][0]['expiry_time'];
            $scanLogModel->data = ['url' => $res['data'][0]['url'], 'admin_id' => $request->id];
            $scanLogModel->save();
            return api_return($res['code'], [
                [
                    'url'=>$this->_scanQrCodeUrl($res['data'][0]['url']),
                    'code_id' => $res['data'][0]['code_id']
                ]
            ]);
        }
        return api_return($res['code'], $res['data'], $res['msg']);
    }




    public function getVerifyCode(Request $request)
    {
        $adminToken = decrypt($request->token);
        if ($adminToken && is_array($adminToken) && $adminToken['type'] == 'create_qr_code' && $adminToken['time'] + (24 * 60 * 60) > time()) {
            $adminId = $adminToken['id'];
            if (AdminScanBind::where('admin_id', $adminId)->count() > 0) {
                // 要扫码验证
                $res = AdminScanLog::createVerifyCode($adminId);
                $type = 4;
            } else {
                // 绑定码
                $res = AdminScanLog::createBindCode($adminId);
                $type = 2;
            }
            if ($res['code'] == '00') {
                $scanLogModel = new  AdminScanLog();
                $scanLogModel->code_id = $res['data'][0]['code_id'];
                $scanLogModel->type = $type;
                $scanLogModel->status = 0;
                $scanLogModel->expiration_time = time() + $res['data'][0]['expiry_time'];
                if($type == 4){
                    $scanLogModel->data = ['url' => $res['data'][0]['url']];
                }else if($type == 2){
                    $scanLogModel->data = ['url' => $res['data'][0]['url'],'admin_id'=>$adminId];
                }
                $scanLogModel->save();
                return api_return($res['code'], [
                    [
                        'url'=>$this->_scanQrCodeUrl($res['data'][0]['url']),
                        'code_id' => $res['data'][0]['code_id']
                    ]
                ]);
            }
            return api_return($res['code'], $res['data'], $res['msg']);
        }
        return api_return(500);
    }


    public function getCodeStatus(Request $request)
    {
        $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
        if ($scanLog->status == 0 && $scanLog->expiration_time <= time()) {
            $scanLog->status = 2;
            $scanLog->save();
        }
        $url = '';
        $token = '';
        $status = 0;
        if ($scanLog->status == 1) {
            $status = 1;
        } else if ($scanLog->status == 2) {
            $status = 2;
        } else if ($scanLog->status == 3) {
//            if($scanLog->type != 2 ){
                $status = 3;
                $url = url('admin/auth/login');
                $token = $scanLog->result['admin_token'];
//            }
        }
        return api_return('00', [$status, $url, $token]);
    }


    /**
     * 生成base 64的二维码
     * @param $url
     * @return string
     */
    private function _scanQrCodeUrl($url){
        $svg =QrCode::size(250)->generate($url);
        $str = rawurlencode($svg);
        $ret = '';
        $len = strlen ( $str );
        for($i = 0; $i < $len; $i ++) {
            if ($str [$i] == '%' && $str [$i + 1] == 'u') {
                $val = hexdec ( substr ( $str, $i + 2, 4 ) );
                if ($val < 0x7f)
                    $ret .= chr ( $val );
                else if ($val < 0x800)
                    $ret .= chr ( 0xc0 | ($val >> 6) ) . chr ( 0x80 | ($val & 0x3f) );
                else
                    $ret .= chr ( 0xe0 | ($val >> 12) ) . chr ( 0x80 | (($val >> 6) & 0x3f) ) . chr ( 0x80 | ($val & 0x3f) );
                $i += 5;
            } else if ($str [$i] == '%') {
                $ret .= urldecode ( substr ( $str, $i, 3 ) );
                $i += 2;
            } else
                $ret .= $str [$i];
        }
        return 'data:image/svg+xml;base64,' .base64_encode($ret);
    }
}
