<?php

namespace Weigather\WJUcenterLoginService\Http\Controllers\Admin;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\UserController;
use Weigather\WJUcenterLoginService\Models\AdminScanBind;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;
use Weigather\WJUcenterLoginService\Models\AdminScanConfig;

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
        $userName = config('admin.database.users_table');
        $roleModel = config('admin.database.roles_model');

        $grid = new Grid(new $userModel());
        if(!Admin::user()->isAdministrator()){
            $adminId = (new $roleModel())->where('slug','administrator')->value('id') ?? 0;
            $adminIds = (new $userModel())->select('id')->with(['roles' => function($query) use($adminId) {
                $query->where('id',$adminId);
            }])->get()->filter(function($val, $key){
                return count($val->roles) < 1;
            })->pluck('id')->toArray() ?? [];
            $grid->model()->whereIn('id',$adminIds);
        }
        $grid->model()->leftjoin('admin_item as t2','t2.admin_id',$userName.'.id')->select($userName.'.*','t2.status as item_admin_id');
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
        }else if(get_wj_ucenter_login_service_version()==4){
            $grid->column('scan_bind_code', '扫码绑定')->display(function () {
                return "<a  target='_blank' href='" . admin_url('wj_scan/bind/' . $this->id) . "'>点击生成二维码</a>";
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

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $item_admin_id = Request::input('item_admin_id');
        $operate_pws = Request::input('operate_pws');
        $userModel = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $userModel());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');
        $form->ignore(['item_admin_id']);
        $form->display('id', 'ID');
        $form->text('username', trans('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);
        $roles = $roleModel::all()->pluck('name', 'id');
        if(!Admin::user()->isAdministrator()){
            $roles = (new $roleModel())->where('slug','<>','administrator')->pluck('name','id');
        }

        $form->multipleSelect('roles', trans('admin.roles'))->options($roles);
        $form->multipleSelect('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));

        $form->switch('item_admin_id','总码登陆')->states(['1','0'])->default(function () use($form) {
            $status = Db::table('admin_item')->where('admin_id',$form->model()->id)->value('status');
            return $status != null ? $status : 0;
        });

        if(config('wj_ucenter_login_service.verify_operate_psw')){
            $form->password('operate_pws','操作密码')->required();
            $form->ignore(['operate_pws']);
        }

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        $form->saving(function (Form $form) use($operate_pws){
            $error = new MessageBag([
                'title'   => '操作密码错误',
                'message' => '',
            ]);
            $config_error = new MessageBag([
                'title'   => '操作密码配置错误！',
                'message' => '',
            ]);

            // 如果开启验证操作密码
            if(config('wj_ucenter_login_service.verify_operate_psw') && !request()->ajax()){
                if(empty(get_config('operate_psw'))){
                    return back()->with(compact('config_error'));
                }
                if(empty($operate_pws)){
                    return back()->with(compact('error'));
                }
                if(!empty($operate_pws) && md5($operate_pws) != get_config('operate_psw')) {
                    return back()->with(compact('error'));
                }
            }
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });
        $form->saved(function (Form $form) use($item_admin_id) {
            $id = $form->model()->id;
            $count = Db::table('admin_item')->where('admin_id',$id)->delete();
            if($item_admin_id == 'on' || $item_admin_id == '1'){
                Db::table('admin_item')->insert(['admin_id'=>$id,'status'=>1]);
            }
        });
        return $form;
    }

    /**
     * 绑定二维码
     * @param Request $request
     * @return mixed
     */
    public function scanBind()
    {
        $key = Request()->route()->parameters()['adminId'];
        $userModel = config('admin.database.users_model');
        $adminUser = (new $userModel)->find($key);
        Admin::script('
            $(document).ready(function(){
                getLoginQrCode("'.$key.'");
            })
        ');
        return Admin::content(function (Content $content) use ($key,$adminUser){
            $content->header(trans('admin::lang.administrator'));
            $content->title('扫码绑定');
            $content->description(trans('扫码绑定'));
            $content->body(view('wj_ucenter_login_service::bind',compact('key','adminUser')));
        });
    }
}
