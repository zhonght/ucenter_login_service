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
        if(class_exists(\Encore\Admin\Admin::class)){
            $version = defined(\Encore\Admin\Admin::VERSION )? \Encore\Admin\Admin::VERSION: 0;
            $intVersion = intval(str_replace('.','',$version));
            if($intVersion<= 160){
                return 2;
            }else if($intVersion<=180){
                return 3;
            }else{
                return 1;
            }
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
