<?php

namespace Encore\WJUcenterLoginService\SDK;

/*
 * 统一用户服务 SDK
 */

class ServiceUserCenter
{
    private $appId;
    private $appSecret;

    public $lang = "zh-CN";// en

    const API_HOST = 'http://ucenter.service.weigather.com/';

    const SCAN_BIND= 'scan/v1/bind';
    const SCAN_LOGIN= 'scan/v1/login';
    const SCAN_VERIFY= '/scan/v1/verify';


    /*
     * ServiceBroadcast constructor.
     * @param $appId 应用的id
     * @param $appSecret 应用密钥
     */
    public function __construct()
    {
        $this->appId = config('wj_ucenter_login_service.user_center.app_id');
        $this->appSecret = config('wj_ucenter_login_service.user_center.app_secret');
    }

    /**
     * 设置语言包 暂时支持  zh-CN en
     * @param $lang
     */
    public function setLang($lang){
        $this->lang = $lang;
    }

    // 应用签名
    public function appIdSign($data = [])
    {
        $data['app_id'] = $this->appId;
        $data['timestamp'] = time();
        $data['nonce_str'] = $this->getNonceStr();
        $data['sign'] = $this->sign($data, $this->appSecret);
        return $data;
    }

    public function accessTokenSign($data = [])
    {
        $data['app_id'] = $this->appId;
        return $data;
    }

    /**
     * 生成签名
     * @param $data
     * @param $key
     * @return string
     */
    public function sign($data, $key)
    {
        ksort($data);
        $data['key'] = $key;
        return md5(http_build_query($data));
    }

    /*
     * 生成随机字符串
     */
    public function getNonceStr($length = 8)
    {
        $charts = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz0123456789";
        $max = strlen($charts);
        $noncestr = "";
        for ($i = 0; $i < $length; $i++) {
            $noncestr .= $charts[mt_rand(0, $max - 1)];
        }
        return $noncestr;
    }


    // 扫码绑定账号
    public function scanBind($nickname ,$username,$avatar,$extend = null){
        $data = [
            'nickname' =>$nickname,
            'username' =>$username,
            'avatar' =>$avatar,
        ];
        if (!is_null($extend)) {
            $data['extend'] = $extend;
        }
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::SCAN_BIND, $data);
        return $res;
    }


    // 扫码登陆账号
    public function scanLogin($extend = null){
        $data = [];
        if (!is_null($extend)) {
            $data['extend'] = $extend;
        }
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::SCAN_LOGIN, $data);
        return $res;
    }


    // 获取扫码授权的链接
    public function scanVerify($nickname ,$username,$avatar,$adminToken ,$extend = null){
        $data = [
            'admin_token'=>$adminToken,
            'nickname' =>$nickname,
            'username' =>$username,
            'avatar' =>$avatar,
        ];
        if (!is_null($extend)) {
            $data['extend'] = $extend;
        }
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::SCAN_VERIFY, $data);
        return $res;
    }

    /*
     * [post http请求函数]
     * @param  [type]  $url    [要请求的地址]
     * @param  array $params [要发送的参数]
     * @param  boolean $post [是否是post请求]
     * @return [type]          [返回的结果数组]
     */
    protected function http($url, $params = array(), $post = true)
    {
        $opts = array(
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => 'gzip',
        );
        /* 根据请求类型设置特定参数 */
        if ($post == false) {
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
        } else {
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($params);
            if (is_string($params)) {
                //发送JSON数据
                $opts[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($params),
                );
            }
        }
        $opts[CURLOPT_HTTPHEADER] = $opts[CURLOPT_HTTPHEADER]??[];
        $opts[CURLOPT_HTTPHEADER][] = 'accept-language:'.$this->lang;
        if(!is_null($this->accessToken)){
            $opts[CURLOPT_HTTPHEADER][] = 'Authorization:'.$this->accessToken;
        }
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $responsedata = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        $data = json_decode($responsedata, true);
        return $data;
    }

}
