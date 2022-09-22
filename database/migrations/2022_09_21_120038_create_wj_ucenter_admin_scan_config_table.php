<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWJUcenterAdminScanConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_scan_config', function (Blueprint $table) {
            $table->increments('id')->comment('id');
            $table->string('key',255)->comment('键');
            $table->longText('value')->comment('值');
            $table->string('group',255)->comment('分组，用于区分不用的配置');
            $table->string('remark',255)->comment('备注');
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
        Schema::dropIfExists('admin_scan_config');
    }
}
