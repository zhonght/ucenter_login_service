<?php


use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Log;
use Weigather\WJUcenterLoginService\Services\BroadcastService;

if (!function_exists('wj_ucenter_login_service_return')) {
    /**
     * 接口返回
     * @param string $code
     * @param null $data
     * @param null $msg
     * @return array
     */
    function wj_ucenter_login_service_return($code = "00", $data = null, $msg = null)
    {
        return [
            'code' => $code,
            'data' => !is_null($data)?$data:[],
            'msg' => !is_null($msg)?$msg:'成功',
        ];
    }
}


if (!function_exists('wj_ucenter_login_service_resource_url')) {
    /**
     * 图片url处理
     * @param $url
     * @return string
     */
    function wj_ucenter_login_service_resource_url($url)
    {
        if (!preg_match('/(http:\/\/)|(https:\/\/)/i', $url)) {
            $url = 'http://' . env('QINIU_DOMAINS', env('APP_URL') . '/upload') . '/' . $url;
        }
        return $url;
    }
}

if (!function_exists('get_wj_ucenter_login_service_version')) {
    function get_wj_ucenter_login_service_version()
    {
        try{
            if(class_exists(\Encore\Admin\Admin::class) &&
                (new ReflectionClass("\Encore\Admin\Admin"))->hasConstant('VERSION')
            ){
                $version = \Encore\Admin\Admin::VERSION;
                if(in_array($version, ['1.8.1','1.8.2','1.8.3','1.8.4','1.8.5','1.8.6','1.8.7','1.8.8','1.8.9','1.8.10'])){
                    return 4;
                }
                $versionArray = explode('.',$version);
                $intVersion = intval($versionArray[0].$versionArray[1]);
                if($intVersion<= 16){
                    return 1;
                }else if($intVersion<=18){
                    return 2;
                }else{
                    return 3;
                }
            }
        }catch (Exception $e){

        }
        return 0;
    }
}


if (!function_exists('admin_asset') && get_wj_ucenter_login_service_version()<=1) {

    /**
     * @param $path
     *
     * @return string
     */
    function admin_asset($path)
    {
        return (config('admin.https') || config('admin.secure')) ? secure_asset($path) : asset($path);
    }
}

if (!function_exists('get_config')) {

    /**
     * @return string
     */
    function get_config($key = 'operate_psw')
    {
        $model = config('wj_ucenter_login_service.verify_operate_psw_model');

        if(!((new $model) instanceof \Illuminate\Database\Eloquent\Model)){
            throw new \Exception('verify_operate_psw model bind error');
        }

        if($model != \Weigather\WJUcenterLoginService\Models\AdminScanConfig::class && !is_subclass_of($model, \Weigather\WJUcenterLoginService\Models\AdminScanConfig::class)){
            throw new \Exception('verify_operate_psw model is not extend appoint class');
        }

        if($model == \Weigather\WJUcenterLoginService\Models\AdminScanConfig::class){
            $res = (new $model)->where('key',$key)->value('value');
            $val = $key == 'operate_psw' ? md5(env('wj_ucenter_login_service.verify_operate_psw_default')) : '';
            if(empty($res) && in_array($key,['operate_psw','wechat_push_user_php_list'])){
                $insert = (new $model)->firstOrCreate(['key' => $key],['value' => $val]);
                $res = $insert->value ?? '';
            }
        }else{
            $table_name = (new $model)->getTable();
            if(!Schema::hasColumn($table_name, 'key') || !Schema::hasColumn($table_name, 'value')){
                throw new \Exception('get verify_operate_psw table column error');
            }
            $res = (new $model)->where('key',$key)->value('value') ?? '';
        }

        if($key == 'operate_psw' && empty($res)){
            throw new \Exception('get verify_operate_psw config error');
        }

        return $res;
    }
}

if (!function_exists('get_config')) {
    function login_push()
    {
        $user_list = explode(',',get_config('wechat_push_user_php_list')) ?? [];
        if(empty($user_list)){
            return '';
        }

        $tmp = [
            'push_id' => '0c57fd07',
            'client'  => 'wechat',
            'user_list' => $user_list,
            'content' => [],//自定义内容
            'data' => [
                'type' => 'text',
                'info' =>
                    [
                        'template_id' => 'jG1DSTSJxmW6voypKbQpUxxy8-ArW95YwcxHpZeLnPs',
                        'url' => '',
                        'data' => [
                            'first' => '登录维度管理后台通知',
                            'keyword1' => Admin::user()->name ?? '',
                            'keyword2' => date('Y-m-d H:i:s'),
                        ]
                    ]
            ]
        ];

        $broad = new BroadcastService();
        try{
            $result = $broad->push($tmp);
        }catch (\Exception $e){
            Log::info(['code'=>$e->getCode(),'msg'=>$e->getMessage(),'line'=>$e->getLine()]) ;
        }
        return $result;
    }
}
