<?php

namespace Encore\WJScanLogin;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class WJScanLoginServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(WJScanLogin $extension)
    {
        if (!WJScanLogin::boot()) {
            return;
        }

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes(
                [
                    $assets => public_path('vendor/laravel-admin-ext/wj_scan_login'),
                    __DIR__ . '/../database/migrations' => database_path('migrations'),
                    __DIR__ . '/../config' => config_path(),
                ],
                'wj_scan_login'
            );
        }


        if (config('wjscanlogin.scan_enable')) {


            Admin::css([
                admin_asset("vendor/laravel-admin-ext/wj_scan_login/css/scan_login.css"),
                admin_asset("vendor/laravel-admin-ext/wj_scan_login/css/scan_login_admin.css")
            ]);
            Admin::html('<script>
            var csrfToken="' . csrf_token() . '";
            var qrCodeLoading="' . admin_asset("vendor/laravel-admin-ext/wj_scan_login/img/qr_code_loading.gif") . '";
            </script>');
            Admin::js([
                admin_asset("vendor/laravel-admin-ext/wj_scan_login/js/scan_bind.js")
            ]);

            if ($views = $extension->views()) {
                $this->loadViewsFrom($views, 'wj_scan_login');
            }
            $this->app->booted(function () {
                WJScanLogin::routes(__DIR__ . '/../routes/web.php');


                // 开放接口出来
                Route::group(array_merge(
                    [
                        'prefix' => config('admin.route.prefix')
                    ],
                    WJScanLogin::config('route', [])
                ), __DIR__ . '/../routes/api.php');

            });
        }


    }

}
