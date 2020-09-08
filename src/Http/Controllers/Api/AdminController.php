<?php

namespace Weigather\WJUcenterLoginService\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Weigather\WJUcenterLoginService\Models\AdminScanBind;
use Weigather\WJUcenterLoginService\Models\AdminScanLog;

/**
 * 后台自己调用的接口
 * Class AdminController
 * @package Encore\WJUcenterLoginService\Http\Controllers\Api
 */
class AdminController extends Controller
{
    /**
     * 验证参数和签名
     * @param Request $request
     * @param $type
     * @return array|bool
     */
    public function checkData(Request $request, $type)
    {
        $validates = [
            'login' => [],
            'status' => [
                'code_id' => 'required|exists:admin_scan_log,code_id',
            ],
            'bind' => [
                'id' => 'required|exists:' . config('admin.database.users_table') . ',id',
            ],
            'verify' => [
                'token' => 'required|string',
            ],
        ];
        $validatedData = Validator::make($request->all(), $validates[$type]);
        if ($validatedData->fails()) {
            return wj_ucenter_login_service_return("500", [], $validatedData->errors()->first());
        }
        return true;
    }

    /**
     * 获取登陆二维码
     * @param Request $request
     * @return array
     */
    public function getLoginCode(Request $request)
    {
        $this->checkData($request, 'login');
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
                    'url' => $this->_scanQrCodeUrl($res['data'][0]['url']),
                    'code_id' => $res['data'][0]['code_id']
                ]
            ]);
        }
        return wj_ucenter_login_service_return($res['code'], $res['data'], $res['msg']);
    }

    /**
     * 获取绑定二维码 这个接口需要登陆后台成功之后才可以调用
     * @param Request $request
     * @return array
     */
    public function getBindCode(Request $request)
    {
        $this->checkData($request, 'bind');
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
                    'url' => $this->_scanQrCodeUrl($res['data'][0]['url']),
                    'code_id' => $res['data'][0]['code_id']
                ]
            ]);
        }
        return wj_ucenter_login_service_return($res['code'], $res['data'], $res['msg']);
    }


    /**
     * 获取验证二维码 如果没有这个账号没有绑定过的话会返回绑定二维码
     * @param Request $request
     * @return array
     */
    public function getVerifyCode(Request $request)
    {
        $this->checkData($request, 'verify');
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
                if ($type == 4) {
                    $scanLogModel->data = ['url' => $res['data'][0]['url']];
                } else if ($type == 2) {
                    $scanLogModel->data = ['url' => $res['data'][0]['url'], 'admin_id' => $adminId];
                }
                $scanLogModel->save();
                return wj_ucenter_login_service_return($res['code'], [
                    [
                        'url' => $this->_scanQrCodeUrl($res['data'][0]['url']),
                        'code_id' => $res['data'][0]['code_id']
                    ]
                ]);
            }
            return wj_ucenter_login_service_return($res['code'], $res['data'], $res['msg']);
        }
        return wj_ucenter_login_service_return('500', [], '系统错误');
    }

    /**
     * 前端轮询二维码状态的接口
     * @param Request $request
     * @return array
     */
    public function getCodeStatus(Request $request)
    {
        $this->checkData($request, 'status');
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
    private function _scanQrCodeUrl($url)
    {
        $svg = QrCode::size(250)->generate($url);
        $str = rawurlencode($svg);
        $ret = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            if ($str [$i] == '%' && $str [$i + 1] == 'u') {
                $val = hexdec(substr($str, $i + 2, 4));
                if ($val < 0x7f)
                    $ret .= chr($val);
                else if ($val < 0x800)
                    $ret .= chr(0xc0 | ($val >> 6)) . chr(0x80 | ($val & 0x3f));
                else
                    $ret .= chr(0xe0 | ($val >> 12)) . chr(0x80 | (($val >> 6) & 0x3f)) . chr(0x80 | ($val & 0x3f));
                $i += 5;
            } else if ($str [$i] == '%') {
                $ret .= urldecode(substr($str, $i, 3));
                $i += 2;
            } else
                $ret .= $str [$i];
        }
        return 'data:image/svg+xml;base64,' . base64_encode($ret);
    }
}
