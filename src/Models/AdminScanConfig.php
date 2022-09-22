<?php

namespace Weigather\WJUcenterLoginService\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 扫码绑定配置的模型
 * Class AdminScanBind
 * @package Encore\WJUcenterLoginService\Models
 */
class AdminScanConfig extends Model
{
    public $table = 'admin_scan_config';

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'remark',
        'group',
    ];
}
