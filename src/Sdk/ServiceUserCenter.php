<?php

namespace Encore\WJScanLogin\SDK;
/*
 * 统一用户服务 SDK
 * 各个接口参数说明查看接口文档
 * http://ucenter.service.weigather.com/swagger/index 正式服
 * http://ucenter.test.meetlan.com/swagger/index 测试服
 */

class ServiceUserCenter
{
    private $appId;
    private $appSecret;
    private $accessToken = null;

    public $lang = "zh-CN";// en

    const API_HOST = 'http://ucenter.service.weigather.com/'; // 正式服
//    const API_HOST = 'http://ucenter.test.meetlan.com/'; // 测试服
//    const API_HOST = 'http://bms.com/'; // 本地

    const LOGIN_URL = 'auth2';
    const BIND_URL = 'bind';

    const REGISTER_URL = 'api/v1/register';
    const PASSWORD_LOGIN_URL = 'api/v1/password_login';
    const SMS_LOGIN_URL = 'api/v1/sms_login';
    const TEMP_CODE_LOGIN_URL = 'api/v1/temp_code_login';
    const USER_INFO_URL = 'api/v1/user_info';
    const GET_ORIGINAL_APP_ID_URL = 'api/v1/get_original_app_id';
    const THIRD_LOGIN_URL = 'api/v1/third_login';
    const CHANGE_PASSWORD_URL = 'api/v1/change_password';
    const MODIFY_INFO_URL = 'api/v1/modify_info';
    const CHECK_OPENID_URL = 'api/v1/check_openid';
    const GET_ALL_PARENTS= 'api/v1/get_all_parents';
    const GET_FATHER= 'api/v1/get_father';
    const GET_SON= 'api/v1/get_son';
    const GET_ALL_CHILDREN= 'api/v1/get_all_children';
    const CAPTCHA= 'api/v1/captcha';
    const CHECK_CAPTCHA= 'api/v1/check_captcha';
    const USER_LEVEL= 'api/v1/user_level';
    const DISTRIBUTE_LEVEL= 'api/v1/distribute_level';
    const ACCESS_TOKEN= 'api/v1/access_token';
    const BIND_PHONE= 'api/v1/bind_phone';

    const SCAN_BIND= 'scan/v1/bind';
    const SCAN_LOGIN= 'scan/v1/login';
    const SCAN_VERIFY= '/scan/v1/verify';


    /*
     * ServiceBroadcast constructor.
     * @param $appId 应用的id
     * @param $appSecret 应用密钥
     */
    public function __construct($accessToken=null)
    {
        $this->appId = 'wj615ca49f15';
        $this->appSecret = 'b2e3475b08d5cf45597907f7fc7c7e04';
        $this->accessToken = $accessToken;
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


    // 注册接口
    public function register($phone= null, $parentUniCode = null, $code = null, $password = '', $userLevelId = 0, $distributeLevelId = 0, $nickname = '', $email = '', $areaCode = 86, $avatar = '', $gender = 0)
    {
        $data = [
            'phone' => $phone,
            'parent_uni_code' => $parentUniCode,
            'code' => $code,
            'password' => $password,
            'user_level_id' => $userLevelId,
            'distribute_level_id' => $distributeLevelId,
            'nickname' => $nickname,
            'email' => $email,
            'area_code' => $areaCode,
            'avatar' => $avatar,
            'gender' => $gender,
        ];
        $data = array_filter($data);
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::REGISTER_URL, $data);
        return $res;
    }


    // 密码登陆接口
    public function passwordLogin($password, $uniCode = null, $phone = null)
    {
        $data = [
            'phone' => $phone
        ];
        if (!is_null($uniCode)) {
            $data['uni_code'] = $uniCode;
        }
        if (!is_null($password)) {
            $data['password'] = $password;
        }
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::PASSWORD_LOGIN_URL, $data);
        return $res;
    }

    // 验证码登陆接口
    public function smsLogin($phone, $code)
    {
        $data = [
            'phone' => $phone,
            'code' => $code
        ];
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::SMS_LOGIN_URL, $data);
        return $res;
    }

    // 临时token登陆
    public function tempCodeLogin($code)
    {
        $data = [
            'code' => $code
        ];
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::TEMP_CODE_LOGIN_URL, $data);
        return $res;
    }

    // 用户数据
    public function userInfo()
    {
        $data = [];
        $data = $this->accessTokenSign($data);
        $res = $this->http(self::API_HOST . self::USER_INFO_URL, $data);
        return $res;
    }

    // 获取用户原始系统id
    public function getOriginalAppId()
    {
        $data = [];
        $data = $this->accessTokenSign($data);
        $res = $this->http(self::API_HOST . self::GET_ORIGINAL_APP_ID_URL, $data);
        return $res;
    }

    // 第三方登陆
    public function thirdLogin($client, $openid, $uniCode = null)
    {
        $data = [
            'client' => $client,
            'openid' => $openid
        ];
        if (!is_null($uniCode)) {
            $data['uni_code'] = $uniCode;
        }
        $data = $this->accessTokenSign($data);
        $res = $this->http(self::API_HOST . self::THIRD_LOGIN_URL, $data);
        return $res;
    }

    // 修改密码
    public function changePassword($password,$code = null, $oldPassword = null)
    {
        $data = [
            'password' => $password,
        ];
        if (!is_null($code)) {
            $data['code'] = $code;
        }
        if (!is_null($oldPassword)) {
            $data['old_password'] = $oldPassword;
        }
        $data = $this->accessTokenSign($data);
        $res = $this->http(self::API_HOST . self::CHANGE_PASSWORD_URL, $data);
        return $res;
    }

    // 修改用户信息
    public function modifyInfo($nickname = null, $avatar = null)
    {
        $data = [];
        if (!is_null($nickname)) {
            $data['nickname'] = $nickname;
        }
        if (!is_null($avatar)) {
            $data['avatar'] = $avatar;
        }
        $data = $this->accessTokenSign($data);
        $res = $this->http(self::API_HOST . self::MODIFY_INFO_URL, $data);
        return $res;
    }

    // 检测openid是否存在用户
    public function checkOpenid($client,$openid)
    {
        $data = [
            'client' => $client,
            'openid' => $openid
        ];
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::CHECK_OPENID_URL, $data);
        return $res;
    }


    // 获取所有上级
    public function allParents()
    {
        $data = [];
        $data = $this->accessTokenSign($data);
        $res = $this->http(self::API_HOST . self::GET_ALL_PARENTS, $data);
        return $res;
    }


    // 获取直属上级
    public function getFather()
    {
        $data = [];
        $data = $this->accessTokenSign($data);
        $res = $this->http(self::API_HOST . self::GET_FATHER, $data);
        return $res;
    }


    // 获取直属下级
    public function getSon()
    {
        $data = [];
        $data = $this->accessTokenSign($data);
        $res = $this->http(self::API_HOST . self::GET_SON, $data);
        return $res;
    }


    // 获取所有的下级
    public function allChildren()
    {
        $data = [];
        $data = $this->accessTokenSign($data);
        $res = $this->http(self::API_HOST . self::GET_ALL_CHILDREN, $data);
        return $res;
    }


    // 获取手机验证码
    public function captcha($phone,$type,$seconds=null,$isTest=null)
    {
        $data = [
            'phone' => $phone,
            'type' => $type
        ];
        if (!is_null($seconds)) {
            $data['seconds'] = $seconds;
        }
        if (!is_null($isTest)) {
            $data['is_test'] = $isTest;
        }
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::CAPTCHA, $data,false);
        return $res;
    }

    // 验证手机验证码
    public function checkCaptcha($phone,$code,$type)
    {
        $data = [
            'phone' => $phone,
            'code' => $code,
            'type' => $type
        ];
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::CHECK_CAPTCHA, $data);
        return $res;
    }

    // 获取用户等级
    public function getUserLevel()
    {
        $data = [];
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::USER_LEVEL, $data,false);
        return $res;
    }

    // 获取分销等级
    public function getDistributeLevel()
    {
        $data = [];
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::DISTRIBUTE_LEVEL, $data,false);
        return $res;
    }

    // 获取access_token
    public function getAccessToken($uniCode)
    {
        $data = [
           'uni_code' =>$uniCode,
        ];
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::ACCESS_TOKEN, $data);
        return $res;
    }

    // 网页授权跳转
    public function toLogin($isApp = null,$url = null,$extra = null){
        $data = [];
        if (!is_null($isApp)) {
            $data['is_app'] = $isApp;
        }
        if (!is_null($url)) {
            $data['url'] = $url;
        }
        if (!is_null($extra)) {
            $data['extra'] = $extra;
        }
        $data = $this->appIdSign($data);
        header('Location: ' . self::API_HOST . self::LOGIN_URL."?" . http_build_query($data));
    }

    // 网页绑定跳转
    public function toBind($isApp = null,$url = null,$extra = null,$client = null,$openid = null){
        $data = [];
        if (!is_null($isApp)) {
            $data['is_app'] = $isApp;
        }
        if (!is_null($url)) {
            $data['url'] = $url;
        }
        if (!is_null($extra)) {
            $data['extra'] = $extra;
        }
        if (!is_null($client)) {
            $data['client'] = $client;
        }
        if (!is_null($openid)) {
            $data['openid'] = $openid;
        }
        $data = $this->appIdSign($data);
        header('Location: ' . self::API_HOST . self::BIND_URL."?" . http_build_query($data));
    }


    // 绑定/修改手机号
    public function bindPhone($phone , $code = null){
        $data = [
            'phone' =>$phone,
        ];
        if (!is_null($code)) {
            $data['code'] = $code;
        }
        $data = $this->appIdSign($data);
        $res = $this->http(self::API_HOST . self::BIND_PHONE, $data);
        return $res;
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
