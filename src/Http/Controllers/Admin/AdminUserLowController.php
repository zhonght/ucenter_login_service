<?php

namespace Weigather\WJUcenterLoginService\Http\Controllers\Admin;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\UserController;
use Encore\Admin\Auth\Database\Administrator;
use Weigather\WJUcenterLoginService\Models\AdminScanBind;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 重写后台用户列表
 * Class AdminUserController
 * @package Encore\WJUcenterLoginService\Http\Controllers\Admin
 */
class AdminUserLowController extends UserController
{
    /**
     * 列表页
     * @param Content $content
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('admin::lang.administrator'));
            $content->description(trans('admin::lang.list'));
            $content->body($this->scanGrid()->render());
        });
    }

    /**
     * 扫码登陆的网格
     * @return Grid
     */
    public function scanGrid()
    {

        return Administrator::grid(function (Grid $grid) {
            $userName = config('admin.database.users_table');
            $grid->model()->leftjoin('admin_item as t2','t2.admin_id',$userName.'.id')->select($userName.'.*','t2.status as item_admin_id');
            $grid->id('ID')->sortable();
            $grid->username(trans('admin::lang.username'));
            $grid->name(trans('admin::lang.name'));
            $grid->roles(trans('admin::lang.roles'))->pluck('name')->label();

            $grid->scan_bind_code('扫码绑定')->display(function () {
                return "<a  target='_blank' href='" . admin_url('wj_scan/bind/' . $this->id) . "'>点击生成二维码</a>";
            });

            $grid->scan_bind_list('绑定列表')->display(function () {
                return "<a  target='_blank' href='" . admin_url('wj_scan/list/' . $this->id) . "'>【" . AdminScanBind::where('admin_id', $this->id)->count() . "】点击查看</a>";
            });

            $grid->column('item_admin_id','总码登陆')->switch(['1','0']);

            $grid->created_at(trans('admin::lang.created_at'));
            $grid->updated_at(trans('admin::lang.updated_at'));

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

            $grid->disableExport();
        });
    }


    /**
     * 绑定列表
     * @param Request $request
     * @return mixed
     */
    public function scanList(Request $request)
    {
        $adminId = $request->adminId;
        return Admin::content(function (Content $content) use ($adminId){
            $content->header(trans('admin::lang.administrator'));
            $content->description(trans('admin::lang.list'));
            $content->body(Admin::grid(AdminScanBind::class, function (Grid $grid) use ($adminId) {
                $grid->id('ID')->sortable();
                $grid->model()->where('admin_id',$adminId);
                $grid->user_token('用户标识');
                $grid->created_at('绑定时间');
                $grid->disableCreation();
                $grid->disableFilter();
                $grid->disablePagination();
                $grid->disableExport();
                $grid->disableRowSelector();
                $grid->actions(function(Grid\Displayers\Actions $actions){
                    $actions->disableEdit();
                });
            }));
        });
    }

    /**
     * 绑定二维码
     * @param Request $request
     * @return mixed
     */
    public function scanBind(Request $request)
    {
        $key = $request->adminId;
        $adminUser = Administrator::find($key);
        return Admin::content(function (Content $content) use ($key,$adminUser){
            $content->header(trans('admin::lang.administrator'));
            $content->description(trans('admin::lang.list'));
            $content->body(view('wj_ucenter_login_service::bind',compact('key','adminUser')));
        });
    }

    /**
     * 删除操作
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function scanBindDestroy(Request $request)
    {
        if ($this->scanBindForm()->destroy($request->id)) {
            return response()->json([
                'status'  => true,
                'message' => trans('admin::lang.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin::lang.delete_failed'),
            ]);
        }
    }

    /**
     * 删除的表单
     * @return mixed
     */
    protected function scanBindForm()
    {
        return Admin::form(
            AdminScanBind::class,
            function (Form $form) {
                $form->display('id', 'ID');
                $form->text('user_token', '用户标识');
            });
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('admin::lang.administrator'));
            $content->description(trans('admin::lang.edit'));
            $content->body($this->scanform()->edit($id));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function scanform()
    {
         return Administrator::form(function (Form $form) {
//            dd($form);
            $userModel = config('admin.database.users_model');
            $permissionModel = config('admin.database.permissions_model');
            $roleModel = config('admin.database.roles_model');

            $userTable = config('admin.database.users_table');
            $connection = config('admin.database.connection');

            $form->ignore(['item_admin_id']);
            $form->display('id', 'ID');


            $form->text('username', '用户名')
                ->creationRules(['required', "unique:{$connection}.{$userTable}"]);
//                ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

            $form->text('name', '名称')->rules('required');
            $form->image('avatar', '头像');
            $form->password('password', '密码')->rules('required|confirmed');
            $form->password('password_confirmation', '确认密码')->rules('required')
                ->default(function ($form) {
                    return $form->model()->password;
                });

            $form->ignore(['password_confirmation']);

            $form->multipleSelect('roles', '角色')->options($roleModel::all()->pluck('name', 'id'));
            $form->multipleSelect('permissions', '权限')->options($permissionModel::all()->pluck('name', 'id'));

            $form->switch('item_admin_id','总码登陆')->states(['1','0'])->default(function () use($form) {
                $status = Db::table('admin_item')->where('admin_id',$form->model()->id)->value('status');
                return $status != null ? $status : 0;
            });
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            $form->saving(function (Form $form) {
                if ($form->password && $form->model()->password != $form->password) {
                    $form->password = Hash::make($form->password);
                }
            });
            $form->saved(function (Form $form){
                Request()->item_admin_id ? $item_admin_id = Request()->item_admin_id : $item_admin_id = "off";
                $id = $form->model()->id;
                $count = Db::table('admin_item')->where('admin_id',$id)->delete();
                if($item_admin_id == 'on'  || $item_admin_id == '1'){
                    Db::table('admin_item')->insert(['admin_id'=>$id,'status'=>1]);
                }
            });
        });
    }

    public function update($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('admin::lang.administrator'));
            $content->description(trans('admin::lang.edit'));
            $content->body($this->scanform()->update($id));
        });
    }
}
