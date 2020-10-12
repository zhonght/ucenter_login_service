<?php

namespace Weigather\WJUcenterLoginService;

use Encore\Admin\Facades\Admin;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WJUcenterLoginServiceServiceProvider extends BaseServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot($extension = null)
    {
        if (!is_null($extension) && !WJUcenterLoginService::boot()) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../resources/assets' => public_path('vendor/weigather/wj_ucenter_login_service'),
                    __DIR__ . '/../database/migrations' => database_path('migrations'),
                    __DIR__ . '/../config' => config_path(),
                ],
                'wj_ucenter_login_service'
            );
        }


        // 如果开了扫码登陆
        if (config('wj_ucenter_login_service.scan_enable')) {
            AliasLoader::getInstance()->alias('QrCode',QrCode::class);
            Admin::css([
//                admin_asset("vendor/weigather/wj_ucenter_login_service/css/scan_login.css"),
                admin_asset("vendor/weigather/wj_ucenter_login_service/css/scan_login_admin.css")
            ]);
            Admin::js([
                admin_asset("vendor/weigather/wj_ucenter_login_service/js/scan_bind.js")
            ]);

            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'wj_ucenter_login_service');

            // 设置扫码相关路由
            $this->app->booted(function () {

                if(get_wj_ucenter_login_service_version()>= 2 ){
                    WJUcenterLoginService::routes(__DIR__ . '/../routes/web.php');

                    // 开放接口出来
                    Route::group(array_merge(
                        [
                            'prefix' => config('admin.route.prefix')
                        ],
                        WJUcenterLoginService::config('route', [])
                    ), __DIR__ . '/../routes/api.php');
                }else{
                    Route::group([
                        'prefix' => 'admin',
                        'middleware' => ['web', 'admin'],
                    ], __DIR__ . '/../routes/web.php');
                    Route::group([
                        'prefix' => 'admin',
                    ], __DIR__ . '/../routes/api.php');
                }

            });
        }


    }

}
