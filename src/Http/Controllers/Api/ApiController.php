<?php

namespace Weigather\WJUcenterLoginService\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\Validator;
use Weigather\WJUcenterLoginService\Models\AdminBossLog;
use Weigather\WJUcenterLoginService\Models\AdminItem;
use Weigather\WJUcenterLoginService\Models\AdminScanLog;
use Weigather\WJUcenterLoginService\Models\AdminScanBind;
use Weigather\WJUcenterLoginService\SDK\ServiceUserCenter;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

/**
 * 提供给用户中心接口的控制器
 * Class ApiController
 * @package Encore\WJUcenterLoginService\Http\Controllers\Api
 */
class ApiController extends Controller
{

    /**
     * 接口验证配置
     * @var array
     */
    public $validates = [
        'status' => [
            'code_id' => 'required|exists:admin_scan_log,code_id',
            'type' => 'required',
        ],
        'bind' => [
            'code_id' => 'required|exists:admin_scan_log,code_id',
            'user_token' => 'required|string',
        ],
        'login' => [
            'code_id' => 'required|exists:admin_scan_log,code_id',
            'token' => 'required|string',
        ],
        'auth' => [
            'code_id' => 'required|exists:admin_scan_log,code_id',
            'token' => 'required|string',
            'user_token' => 'required|string',
            'is_login' => 'required',
        ],
        'users' => [
            'code_id' => 'required|exists:admin_scan_log,code_id',
            'token' => 'required|string',
        ],
        'bossUsers' => [
        ],
        'bossLogin' => [
            'code_id' => 'required|exists:admin_boss_log,code_id',
            'token' => 'required|string',
        ],
    ];


    /**
     * 验证参数和签名
     * @param Request $request
     * @param $type
     * @return array|bool
     */
    public function checkData(Request $request, $type)
    {
        $validatedData = Validator::make($request->all(), array_merge([
            'app_id' => 'required|string|in:' . config('wj_ucenter_login_service.user_center.app_id'),
            'timestamp' => 'required|numeric',
            'nonce_str' => 'required|string',
            'sign' => 'required|string',
            'extend' => 'nullable|string'
        ], $this->validates[$type]));
        if ($validatedData->fails()) {
            return wj_ucenter_login_service_return("500", [], $validatedData->errors()->first());
        }
        $sdkModel = new ServiceUserCenter();
        if ($sdkModel->sign($request->except('sign'), config('wj_ucenter_login_service.user_center.app_secret')) != $request->sign) {
            return wj_ucenter_login_service_return('100005', [], '签名错误');
        }
        return true;
    }

    /**
     * 二维码状态回调
     * @param Request $request
     * @return array
     */
    public function status(Request $request): array
    {
        $this->checkData($request, 'status');
        $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
        if ($request->type == 1) {
            $scanLog->status = 1;
        }
        $scanLog->scan_status = $request->type;
        $scanLog->save();
        return wj_ucenter_login_service_return('200', [], '操作成功');
    }

    /**
     * ‌登录回调
     * @param Request $request
     * @return array
     */
    public function bossLogin(Request $request): array
    {
        $code_id = request()->code_id;
        $token = request()->token;
        $this->checkData($request, 'bossLogin');
        $scanLog = AdminBossLog::where('code_id', $code_id)->first();
        $scanLog->status = 3;
        $scanLog->result = ['boss_token' => $token];
        $scanLog->save();
        return wj_ucenter_login_service_return('200', [], '‌登录成功');
    }
    /**
     * 获取总码用户
     * @param Request $request
     * @return array
     */
    public function bossUsers(Request $request)
    {
        $this->checkData($request, 'bossUsers');
        $admin_boss_log = [
            'code_id' => $request->code_id ?? 0,
            'type' => '3',
            'user_token' => $request->user_token ?? 'null',
            'status' => 1,
            'scan_status' => 1,
            'data' => json_encode($request->extend ?? []),
//            'data' => $request->extend ?? '{}',
        ];
        $extend = empty($request->extend) ? [] : $request->extend;
        AdminBossLog::insert($admin_boss_log);
        $admin = AdminItem::get()->map(function (AdminItem $map) use ($extend) {
            $adminUser = $map->admin;
            $token = encrypt(['type' => 'login_token', 'time' => time(), 'admin_id' => $map->admin_id, 'username' => $adminUser->username]);
            Redis::setex('boss_' . $adminUser->username, 60, $token);
            return [
                'nickname' => $adminUser->name,
                'username' => $adminUser->username,
                'avatar' => wj_ucenter_login_service_resource_url($adminUser->avatar),
                'token' => $token,
                'url' => admin_url('auth/boss_login') . '?admin_token=' . $token,
                'extend' => array_merge($extend, ['item_url' => admin_url('auth/boss_login') . '?boss=' . $token]),
            ];
        });
        $admin = $admin->toArray();
        if (empty($admin)) {
            return wj_ucenter_login_service_return('500', [], '当前项目无可‌登录账号');
        } else {
            return wj_ucenter_login_service_return('200', $admin);
        }
    }

    /**
     * 绑定回调
     * @param Request $request
     * @return array
     */
    public function bind(Request $request)
    {
        $this->checkData($request, 'bind');
        $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
        $scanLog->status = 3;
        $scanLog->user_token = $request->user_token;
        $scanLog->result = ['admin_token' => encrypt(['id' => $scanLog->data['admin_id'], 'type' => 'login_token', 'time' => time()])];
        $scanLog->save();
        if (AdminScanBind::where('admin_id', $scanLog->data['admin_id'])->where('user_token', $request->user_token)->count() > 0) {
            return wj_ucenter_login_service_return('200', [], '该账号已经绑定过,无需重复绑定');
        }
        $scanUser = new AdminScanBind();
        $scanUser->admin_id = $scanLog->data['admin_id'];
        $scanUser->user_token = $request->user_token;
        $scanUser->save();
        return wj_ucenter_login_service_return('200', [], '绑定成功');
    }

    /**
     * ‌登录回调
     * @param Request $request
     * @return array
     */
    public function login(Request $request)
    {
        $this->checkData($request, 'login');
        $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
        $scanLog->status = 3;
        $scanLog->result = ['admin_token' => $request->token];
        $scanLog->save();
        return wj_ucenter_login_service_return('200', [], '‌登录成功');
    }

    /**
     * 验证回调
     * @param Request $request
     * @return array
     */
    public function auth(Request $request)
    {
        $this->checkData($request, 'auth');
        $adminToken = decrypt($request->token);
        if ($adminToken && is_array($adminToken) && $adminToken['type'] == 'verify_qr_code' && $adminToken['time'] + (24 * 60 * 60) > time()) {
            $adminId = $adminToken['id'];
            if (AdminScanBind::where('admin_id', $adminId)->where('user_token', $request->user_token)->count() > 0) {

                if ($request->is_login) {
                    $scanLog = AdminScanLog::where('code_id', $request->code_id)->first();
                    $scanLog->status = 3;
                    $scanLog->user_token = $request->user_token;
                    $scanLog->result = ['token' => $request->token, 'admin_token' => encrypt(['id' => $adminId, 'type' => 'login_token', 'time' => time()])];
                    $scanLog->save();
                    return wj_ucenter_login_service_return('200', [], '确认成功');
                }

                return wj_ucenter_login_service_return('200', [], '检测成功');
            }
            return wj_ucenter_login_service_return('500', [], '您不是该账户的管理员,请检查微信号是否正确');
        }
        return wj_ucenter_login_service_return('500', [], 'token非法');
    }

    /**
     * 获取用户列表
     * @param Request $request
     * @return array
     */
    public function users(Request $request)
    {
        $this->checkData($request, 'users');

        // 判断是否总码‌登录
        $if_item_scan = false;
        if (isset($request->extend) && $request->extend) {
            $extend = json_decode($request->extend, true);
            if ($extend['if_item_scan'] === true) {
                $if_item_scan = true;
            }
        }

        if ($if_item_scan === true) {
            // admin_scan_log
            $admin_scan_log = [
                'code_id' => $request->code_id,
                'type' => '3',
                'user_token' => $request->user_token,
                'status' => 1,
                'scan_status' => 1,
                'data' => $request->extend,
            ];
            AdminScanLog::insert($admin_scan_log);

            $item_id = Db::table('admin_item')->pluck('admin_id')->toArray();
            $item_id = $item_id ? $item_id : [];
            $admin_id = AdminScanBind::where('user_token', $request->user_token)->pluck('id')->toArray();
            $admin_id = $admin_id ?? [];
            $where_id = array_merge($item_id, $admin_id);
            $where_id = $where_id ?? [0];

            $admin = Administrator::whereIn('id', $where_id)->get()->map(function ($map) {
                $token = encrypt(['id' => $map->id, 'type' => 'login_token', 'time' => time(), 'name' => $map->name]);
                Redis::setex('item_' . $map->name, 60, $token);
                return [
                    'nickname' => $map->name,
                    'username' => $map->username,
                    'avatar' => wj_ucenter_login_service_resource_url($map->avatar),
                    'token' => $token,
                    'extend' => ['item_url' => admin_url('auth/item_login') . '?admin_token=' . $token],
                ];
            });
            $admin = $admin->toArray();
            return wj_ucenter_login_service_return('200', $admin);
        } else {
            return wj_ucenter_login_service_return('200', AdminScanBind::where('user_token', $request->user_token)->get()->map(function ($map) {
                $admin = Administrator::find($map->admin_id);
                return [
                    'nickname' => $admin->name,
                    'username' => $admin->username,
                    'avatar' => wj_ucenter_login_service_resource_url($admin->avatar),
                    'token' => encrypt(['id' => $admin->id, 'type' => 'login_token', 'time' => time()]),
                ];
            }));
        }
    }


}
