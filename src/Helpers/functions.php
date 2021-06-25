<?php


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
