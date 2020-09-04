<?php

namespace Encore\WJUcenterLoginService\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Encore\WJUcenterLoginService\Models\AdminScanBind;
use Encore\WJUcenterLoginService\Models\AdminScanLog;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AdminController extends Controller
{

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
            return wj_ucenter_login_service_return($res['code'], [
                [
                    'url'=>$this->_scanQrCodeUrl($res['data'][0]['url']),
                    'code_id' => $res['data'][0]['code_id']
                ]
            ]);
        }
        return wj_ucenter_login_service_return($res['code'], $res['data'], $res['msg']);
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
            return wj_ucenter_login_service_return($res['code'], [
                [
                    'url'=>$this->_scanQrCodeUrl($res['data'][0]['url']),
                    'code_id' => $res['data'][0]['code_id']
                ]
            ]);
        }
        return wj_ucenter_login_service_return($res['code'], $res['data'], $res['msg']);
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
                return wj_ucenter_login_service_return($res['code'], [
                    [
                        'url'=>$this->_scanQrCodeUrl($res['data'][0]['url']),
                        'code_id' => $res['data'][0]['code_id']
                    ]
                ]);
            }
            return wj_ucenter_login_service_return($res['code'], $res['data'], $res['msg']);
        }
        return wj_ucenter_login_service_return('500',[],'系统错误');
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
                $status = 3;
                $url = url('admin/auth/login');
                $token = $scanLog->result['admin_token'];
        }
        return wj_ucenter_login_service_return('00', [$status, $url, $token]);
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
