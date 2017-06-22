<?php
namespace Wechat\Api;

/**
* @description 微信接口核心类
* @author shf1986@gmail.com
* @version 1.0
* @createDate 2015/1/7
* @modify
* @error code
* 	
*/
class Core
{
    //接口基础地址
    const WEIXIN_HOST      = 'https://api.weixin.qq.com';  

    //获取access_token_url地址
    const GET_TOKEN       = '/cgi-bin/token';

    //获取微信服务器IP地址
    const GET_CALLBACK_IP = '/cgi-bin/getcallbackip';  

    //获取jsticket
    const GET_TICKET  = '/cgi-bin/ticket/getticket';

    //token过期时间
    const TOKEN_EXPIRED_TIME = 3600;

    //refresh token error
    protected $refresh_token_errno = array(40001,40014,42001);

    //access token
    protected $token;
    //错误提示
    protected $error_msg = '';
    //token url
    protected $token_url;

    public function __construct() 
    {
    }
    
    public function __destruct() 
    {
        //Nothing
    }

    /**
    * curl 请求
    **/
    protected function request()
    {
        $request = new \Util\Http();
        $request->curlOpts = array(CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false);
        return $request;
    }

    /**
    * get获取数据
    * @param $params - 请求的参数 
    * @param $url - api地址
    * @return mixed
    **/
    protected function getData($params, $api, $key = '')
    {
        $request = $this->request();
        if (strpos($api, 'http') === false) {
            $request->url = self::WEIXIN_HOST . $api;
        } else {
            $request->url = $api;
        }
        $request->parameters = array_merge(
            array(
                'access_token' => $this->token
            ),
            $params
        );
        $response = $request->get();
        $return = json_decode($response->body, true);
        
        if (empty($return['errcode'])) {
            if (empty($return[$key])) {
                if (!empty($return['errmsg']) && ('ok' == $return['errmsg'])) {
                    return true;
                } else {
                    return $return;
                }
            } else {
                return $return[$key];
            }
        } else {
            $this->setError($return);
            return false;
        }
    }

    /**
    * post提交数据
    * @param $params - 请求的参数 
    * @param $url - api地址
    * @return mixed
    **/
    protected function postData($data, $api, $key = '', $query = array())
    {
        $request = $this->request();
        if (strpos($api, 'http') === false) {
            $request->url = self::WEIXIN_HOST . $api;
        } else {
            $request->url = $api;
        }
        $request->parameters = array_merge(
            array (
                'access_token' => $this->token
            ),
            $query
        );
        if (isset($data['file'])) {
            $request->body = array('media' => '@' . $data['file']);
        } elseif ($data) {
            $request->body = urldecode(json_encode($data));
        }
        $response = $request->post();

        $return = json_decode($response->body, true);

        $error = json_last_error();
        if (JSON_ERROR_UTF8 == $error) {
            $return = json_decode(iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($response->body)), true);
            $return['is_utf8'] = true;
        } elseif (JSON_ERROR_CTRL_CHAR == $error) {
            $return = json_decode(preg_replace('/[\x00-\x1F]/', '', $response->body), true);
        }
        
        if (empty($return['errcode'])) {
            if (empty($return[$key])) {
                if (!empty($return['errmsg']) && ('ok' == $return['errmsg'])) {
                    return true;
                } else {
                    return $return;
                }
            } else {
                return $return[$key];
            }
        } else {
            $this->setError($return);
            return false;
        }
    }

    /**
    * 获取access token
    * @param int $refresh 
    * @return string $token
    **/
    public function getAccessToken($url)
    {
        $this->token_url = $url;
        return $this->refreshAccessToken();
    }

    /**
    * 获取access token
    * @param int $refresh 
    * @return string $token
    **/
    public function refreshAccessToken()
    {
        //远程接口调用
        $request = $this->request();
        $request->url = $this->token_url;
        
        $response = $request->post();

        $return = json_decode($response->body, true);
        if (0 == $return['error']) {
            $this->token = $return['data'];
            return $this->token;
        } else {
            return false; 
        }
    }

    /**
     * setAccessToken
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-03-06
     *
     * @return void
     */
    public function setAccessToken($token)
    {
        if (empty($token)) {
            return false;
        }
        $this->token = $token;
    }

    /**
    * 获取微信服务器IP地址   
    * @return string $iplist
    **/
    public function getCallBackIp()
    {
        $data = array();
        return $this->getData($data, self::GET_CALLBACK_IP, 'ip_list');
    }

    /**
    * 设置错误
    **/
    public function setError($error)
    {
        if (isset($error['errcode']) 
            && in_array($error['errcode'], $this->refresh_token_errno)) {
            $this->refreshAccessToken();
        }
        $this->error_msg = $error;
    }

    /**
    * 获取接口调用错误信息
    **/
    public function getError($format = false)
    {
        if ($format) {
            return json_encode($this->error_msg);
        } else {
            return $this->error_msg;
        }
    }

    private function getJsApiTicket($refresh = 0)
    {
        //暂不使用
    }

    /**
    ** 获取jsapi 签名
    **/
    public function getSignPackage($appId, $url = '')
    {
        $jsapiTicket = $this->getJsApiTicket();
        if (empty($url)) {
            $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        //这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
          "appId"     => $appId,
          "nonceStr"  => $nonceStr,
          "timestamp" => $timestamp,
          "url"       => $url,
          "signature" => $signature,
          "rawString" => $string
        );
        return $signPackage; 
    }

    /**
    * 获取位置签名生成
    **/
    public function getSignAddr($url = '')
    {
        if (empty($url)) {
            $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        //这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "accesstoken=" . $this->token . "&appId=" . Config::item('wx_app_id') . "&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        return sha1($string); 
    }

    protected function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
          $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}
?>
