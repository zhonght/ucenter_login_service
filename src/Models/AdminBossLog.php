<?php

namespace Weigather\WJUcenterLoginService\Models;


use Illuminate\Database\Eloquent\Model;

class AdminBossLog extends Model
{
    public $table = 'admin_boss_log';

    protected $casts = [
        'data' => 'json',
        'result' => 'json',
    ];
}
