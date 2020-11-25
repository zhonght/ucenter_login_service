<?php

namespace Weigather\WJUcenterLoginService\Http\Controllers\Admin;

use Encore\Admin\Grid;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\UserController;
use Weigather\WJUcenterLoginService\Models\AdminScanBind;

/**
 * 重写后台用户列表
 * Class AdminUserController
 * @package Encore\WJUcenterLoginService\Http\Controllers\Admin
 */
class AdminUserController extends UserController
{
    /**
     * 列表页
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body($this->scanGrid());
    }

    /**
     * 扫码登陆的网格
     * @return Grid
     */
    public function scanGrid(){
        $userModel = config('admin.database.users_model');
        $grid = new Grid(new $userModel());
        $grid->column('id', 'ID')->sortable();
        $grid->column('username', trans('admin.username'));
        $grid->column('name', trans('admin.name'));
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label();

        if(get_wj_ucenter_login_service_version()==3){
            $grid->column('scan_bind_code', '扫码绑定')->display(function () {
                return "点击生成二维码";
            })->modal( "绑定二维码",Weigather\WJUcenterLoginService\Render\BindCode::class);
        }else if(get_wj_ucenter_login_service_version()==2){
            $grid->column('scan_bind_code', '扫码绑定')->display(function () {
                return "<span onclick='getLoginQrCode(\"{$this->id}\");'>点击生成二维码</span>";
            })->modal( "绑定二维码",function($model){
                $key = $this->id;
                $adminUser = $model->find($key);
                $csrfToken = csrf_token();
                $qrCodeLoading = admin_asset("vendor/weigather/wj_ucenter_login_service/img/qr_code_loading.gif");
                return view('wj_ucenter_login_service::bind',compact('key','adminUser','csrfToken','qrCodeLoading'));
            });
        }
        

        $grid->column('scan_bind_list', '绑定列表')->display(function () {
            return "【".AdminScanBind::where('admin_id',$this->id)->count()."】点击查看";
        })->expand(function ($model) {
            $temp = AdminScanBind::where('admin_id',$model->id)->get();
            if(count($temp) > 0 ){
                $comments = $temp->map(function ($comment) {
                    return [
                        $comment->id,
                        $comment->user_token,
                        $comment->created_at,
                        '<a data-_key="'.$comment->id.'" href="javascript:void(0)" onclick="deleteBindInfo(this);" class="grid-row-action-scan-unbind">解绑</a>'
                    ];
                })->toArray();
            }else{
                $comments = [['-','-','-','-']];
            }
            return new Table(['id', '用户标识', '绑定时间', '操作'],$comments);
        });

        $grid->column('item_admin_id','总码登陆')->switch(['1','0']);

        $grid->column('created_at', trans('admin.created_at'));
        $grid->column('updated_at', trans('admin.updated_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }
}
