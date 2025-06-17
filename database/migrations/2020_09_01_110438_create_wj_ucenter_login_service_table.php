<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWJUcenterLoginServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_scan_bind', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('admin_id')->index()->comment('账号id');
            $table->string('user_token',128)->comment('绑定的用户');
            $table->timestamps();
        });
        Schema::create('admin_scan_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code_id',128)->comment('二维码编号');
            $table->tinyInteger('type')->default(0)->comment('二维码类型 1 ‌登录 2 绑定 4 授权');
            $table->string('user_token',128)->nullable()->comment('用户编号');
            $table->tinyInteger('status')->default(0)->comment('二维码状态  0 未使用 1 已扫码 2 已过期 3 已完成');
            $table->tinyInteger('scan_status')->default(0)->comment('扫码人状态  0 未使用 1 已扫码 2 已过期 3 未绑定进行扫码  4 取消扫码');
            $table->integer('expiration_time')->default(0)->comment('过期时间的时间戳');
            $table->text('data')->nullable()->comment('附加数据 json');
            $table->text('result')->nullable()->comment('结果 json');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_scan_bind');
        Schema::dropIfExists('admin_scan_log');
    }
}
