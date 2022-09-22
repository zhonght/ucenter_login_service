<?php
namespace Weigather\WJUcenterLoginService\Services;

class BroadcastService
{
    private $appId;
    private $appSecret;
    private $apiHost;
    //const API_HOST =  'http://broadcast.service.weigather.com/'; // 正式服
    //const API_HOST =  'http://broadcast.test.meetlan.com'; // 测试服

    const SEND_URL = '/api/v1/broadcast/send';
    const STATUS_URL = '/api/v1/broadcast/status';
    const STOP_URL = '/api/v1/broadcast/stop';
    const SUBSCRIBE_REGISTER_URL = '/api/v1/subscribe/register';
    const SUBSCRIBE_UPDATE_URL = '/api/v1/subscribe/update';
    const SUBSCRIBE_REMOVE_URL = '/api/v1/subscribe/remove';
    const SUBSCRIBE_DISABLE_URL = '/api/v1/subscribe/disable';
    const SUBSCRIBE_ENABLE_URL = '/api/v1/subscribe/enable';
    const PUSH_SEND_URL = '/api/v1/push/send';
    const PUSH_USERS_URL = '/api/v1/push/users';


    /*
     * ServiceBroadcast constructor.
     * @param $appId 应用的id
     * @param $appSecret 应用密钥
     */
    public function __construct()
    {
        $config = config('wj_ucenter_login_service.broadcast');
        $this->appId = $config['app_key'];
        $this->appSecret = $config['app_secret'];
        $this->apiHost = $config['app_url'];
    }

    /*
     * [send 发送请求]
     * @param  [array]  $header     [需要原样发送的请求头]
     * @param  [array]  $params     [需要原样发送的数据]
     * @param  [array]  $tasks      [任务数组]
     * @return [array]              [返回数组,code = '00'为成功 msg ：文字提示 data： 数据 ]
     * 接口参数
     *  app_id             string      应用的id
     *  nonce_str          string      随机加字符串
     *  timestamp          int         10位数时间戳
     *  sign_type          string      加密类型 暂只支持 md5 不传默认md5
     *  task_headers       array       需要原样发送的请求头
     *  task_params        array       需要原样发送的数据
     *  sign               string      签名
     *  tasks              array       任务配置
     *  --  code           string      任务编号 （类似订单号）
     *  --  method         string      发送类型 post get
     *  --  url            string      发送地址
     *  --  response       array       响应配置
     *      -- type        string      响应解析类型 string json xml
     *      -- condition   string      响应解析条件 响应解析类型为string的时候传需要匹配的字符串，如“success” 响应解析类型为json或者xml的时候传需要匹配的key:value，如“code:00” 多项的时候可以用 && 和 || 进行逻辑运算，暂不支持 && 和 || 混合使用
     *  --  retry          array       重试配置
     *      -- type        int         重试类型  0 不重试  1 等差  2 等比
     *      -- max         int         最大重试次数  最多传10 不传默认 5 次   重试类型为0不用传
     *      -- initial     int         初始值  重试类型为 0 不用传  重试类型为 1 或者 2 的时候为首项（单位分钟 正整数 ）
     *      -- value       int         重试参数  重试类型为 0 不用传 重试类型为 1 填写公差（单位分钟 正整数） 重试类型为 2 填写公比（正整数）
     *
     * 返回code为 00 的时候 data 数据如下
     *
     *  {
     *       "main_code": "123456789",  // 主任务编号
     *       "tasks": {
     *           "任务编号1": "123456",   // 子任务编号
     *           "任务编号2": "456789"    // 子任务编号
     *       }
     *   }
     *
     *  测试用例 /test/send
     *
     */
    public function send(array $header,array $params,array $tasks,$isSyncCall = 0)
    {
        $data = self::post($this->apiHost.self::SEND_URL, $this->sign([
            'task_headers' => $header,
            'task_params' => $params,
            'tasks' => $tasks,
            'is_sync_call' => $isSyncCall
        ]));
        return $data;
    }

    /*
     * [status 查看广播状态]
     * @param  [string]  main_codes    [主任务编号，多个用英文逗号隔开]
     * @param  [string]  sub_codes     [子任务编号，多个用英文逗号隔开]
     * @return [array]                  [返回数组,code = '00'为成功 msg ：文字提示 data： 数据 ]
     *
     *  测试用例 /test/send_status
     *
     */
    public function status(array $mainTasks = [],array $subTasks = [])
    {
        $params = [];
        if (!empty($mainTasks)){
            $params['main_codes'] =  implode(',',$mainTasks);
        }
        if (!empty($subTasks)){
            $params['sub_codes'] =   implode(',',$subTasks);
        }
        $data = self::post($this->apiHost.self::STATUS_URL, $this->sign($params));
        return $data;
    }


    /*
     * [stop 暂停广播]
     * @param  [string]  main_codes    [主任务编号，多个用英文逗号隔开]
     * @param  [string]  sub_codes     [子任务编号，多个用英文逗号隔开]
     * @return [array]                  [返回数组,code = '00'为成功 msg ：文字提示 data： 数据 ]
     *
     *  测试用例 /test/send_status_stop
     *
     */
    public function stop(array $mainTasks = [],array $subTasks = [])
    {
        $params = [];
        if (!empty($mainTasks)){
            $params['main_codes'] =  implode(',',$mainTasks);
        }
        if (!empty($subTasks)){
            $params['sub_codes'] =   implode(',',$subTasks);
        }
        $data = self::post($this->apiHost.self::STOP_URL, $this->sign($params));
        return $data;
    }

    /*
     * [注册订阅]
     * @param  [array]  $tasks      [任务数组]
     * @return [array]              [返回数组,code = '00'为成功 msg ：文字提示 data： 数据 ]
     * 接口参数
     *  tasks              array       任务配置
     *  --  code           string      任务编号 （类似订单号）
     *  --  method         string      发送类型 post get
     *  --  url            string      发送地址
     *  --  response       array       响应配置
     *      -- type        string      响应解析类型 string json xml
     *      -- condition   string      响应解析条件 响应解析类型为string的时候传需要匹配的字符串，如“success” 响应解析类型为json或者xml的时候传需要匹配的key:value，如“code:00” 多项的时候可以用 && 和 || 进行逻辑运算，暂不支持 && 和 || 混合使用
     *  --  retry          array       重试配置
     *      -- type        int         重试类型  0 不重试  1 等差  2 等比
     *      -- max         int         最大重试次数  最多传10 不传默认 5 次   重试类型为0不用传
     *      -- initial     int         初始值  重试类型为 0 不用传  重试类型为 1 或者 2 的时候为首项（单位分钟 正整数 ）
     *      -- value       int         重试参数  重试类型为 0 不用传 重试类型为 1 填写公差（单位分钟 正整数） 重试类型为 2 填写公比（正整数）
     *
     * 返回code为 00 的时候 data 数据如下
     *
     *  {
     *       "code": "123456789",  // 订阅编号
     *       "url": "http://xx.com/xx" // 订阅回调地址
     *   }
     *
     *  测试用例 /test/subscribe
     *
     */
    public function subscribeRegister(array $tasks)
    {
        $data = self::post($this->apiHost.self::SUBSCRIBE_REGISTER_URL, $this->sign([
            'tasks' => $tasks,
        ]));
        return $data;
    }

    public function subscribeUpdate(string $code,array $tasks)
    {
        $data = self::post($this->apiHost.self::SUBSCRIBE_UPDATE_URL, $this->sign([
            'code' => $code,
            'tasks' => $tasks,
        ]));
        return $data;
    }

    public function subscribeRemove(string $code)
    {
        $data = self::post($this->apiHost.self::SUBSCRIBE_REMOVE_URL, $this->sign([
            'code' => $code
        ]));
        return $data;
    }

    public function subscribeDisable(string $code)
    {
        $data = self::post($this->apiHost.self::SUBSCRIBE_DISABLE_URL, $this->sign([
            'code' => $code
        ]));
        return $data;
    }

    public function subscribeEnable(string $code)
    {
        $data = self::post($this->apiHost.self::SUBSCRIBE_ENABLE_URL, $this->sign([
            'code' => $code
        ]));
        return $data;
    }



    /*
     * [推送]
     * @param  [array]  $data      [任务数组]
     * @return [array]              [返回数组,code = '00'为成功 msg ：文字提示 data： 数据 ]
     * 接口参数
     *  data              array       任务配置
     *  --  push_id       string      推送编号
     *  --  client        string      推送类型 wechat,dingtalk,flymouse
     *  --  user_list     array       对应推送类型的用户id
     *  --  data          array       响应配置
     *     -- type        string      类型 暂时只支持 text
     *
     * 返回时的 data 没有数据
     */


    public function push(array $data)
    {
        $data = self::post($this->apiHost.self::PUSH_SEND_URL, $this->sign([
            'data'=>json_encode($data,JSON_UNESCAPED_UNICODE)
        ]));
        return $data;
    }

    /*
     * [获取某个推送服务某种推送类型的用户列表]
     * @param  [array]  $data      [任务数组]
     * @return [array]              [返回数组,code = '00'为成功 msg ：文字提示 data： 数据 ]
     * 接口参数
     *  push_id           string      推送编号
     *  client            string      推送类型 wechat,dingtalk,flymouse
     *
     * 返回时的 data
     *  data              array
     *  --  id            string      用户id 发起推送的接口要用到
     *  --  nickname      string      用户名称
     *  --  avatar        string      用户头像
     *  --  remarks       string      用户备注
     *  --  tel           string      钉钉分机号
     */
    public function pushUsers(string $pushId,string $client)
    {
        $data = self::post($this->apiHost.self::PUSH_USERS_URL, $this->sign([
            'push_id'=>$pushId,
            'client'=>$client
        ]));
        return $data;
    }


    /**
     * 签名
     * @param $params
     * @param string $signType
     * @return mixed
     */
    protected function sign($params,$signType = 'md5')
    {
        $params['app_id'] = $this->appId;
        $params['nonce_str'] = $this->getNonceStr(10);
        $params['timestamp'] = time();
        $params['sign_type'] = $signType;
        if($signType == 'md5'){
            $params['sign'] = self::md5Sign($params, $this->appSecret);
        }
        return $params;
    }
    /**
     * md5加密
     * @param $data
     * @param $key
     * @return string
     */
    protected static function md5Sign($data, $key)
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


    /*
     * [post http请求函数]
     * @param  [type]  $url    [要请求的地址]
     * @param  array $params [要发送的参数]
     * @param  boolean $post [是否是post请求]
     * @return [type]          [返回的结果数组]
     */
    protected static function post($url, $params = array(), $post = true)
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
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $responsedata = curl_exec($ch);
        $error = curl_error($ch);
//        dd($params,$responsedata,$error);
        curl_close($ch);
        $data = json_decode($responsedata, true);
        return $data;
    }

}
