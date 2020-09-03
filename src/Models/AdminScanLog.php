<?php

namespace Encore\WJScanLogin\Models;

use Encore\WJScanLogin\SDK\ServiceUserCenter;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Model;

class AdminScanLog extends Model
{
    public $table = 'admin_scan_log';


    protected $casts = [
        'data' => 'json',
        'result' => 'json',
    ];


    public static function createLoginCode()
    {
        $model = new ServiceUserCenter();
        return $model->scanLogin();
    }

    public static function createVerifyCode($adminId)
    {
        $userModel = config('admin.database.users_model');
        $adminUser = $userModel::query()->find($adminId);
        $model = new ServiceUserCenter();
        return $model->scanVerify($adminUser->name, $adminUser->username, resource_url($adminUser->avatar), encrypt([
            'type' => 'verify_qr_code',
            'id' => $adminUser->id,
            'time' => time()
        ]));
    }

    public static function createBindCode($adminId)
    {
        $userModel = config('admin.database.users_model');
        $adminUser = $userModel::query()->find($adminId);
        $model = new ServiceUserCenter();
        return $model->scanBind($adminUser->name, $adminUser->username, resource_url($adminUser->avatar));
    }

}
