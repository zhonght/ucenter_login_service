<?php

namespace Weigather\WJUcenterLoginService\Models;


use Illuminate\Database\Eloquent\Model;

class AdminItem extends Model
{
    public $table = 'admin_item';


    public function admin()
    {
        $userModel = config('admin.database.users_model');
        return $this->hasOne(new $userModel,'id','admin_id');
    }
}
