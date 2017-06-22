<?php
namespace Util;

/**
 * CURL 
 *
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-06-19
 */
class Http
{
    /**
     * url
     *
     * @var mixed
     */
    public $url = null;

    /**
     * parameters
     *
     * @var array
     */
    public $parameters = [];

    /**
     * headers
     *
     * @var mixed
     */
    public $headers = null;

    /**
     * cookies
     *
     * @var mixed
     */
    public $cookies = null;

    /**
     * body
     *
     * @var mixed
     */
    public $body = null;

    /**
     * followRedirect
     *
     * @var mixed
     */
    public $followRedirect = true;

    /**
     * maxRedirect
     *
     * @var int
     */
    public $maxRedirect = 3;

    /**
     * numRedirect
     *
     * @var int
     */
    public $numRedirect = 0;

    /**
     * timeout
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * curlOpts
     *
     * @var array
     */
    public $curlOpts = [];

    /**
     * post
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public function post()
    {
        return HttpClient::post($this);
    }

    /**
     * get
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public function get()
    {
        return HttpClient::get($this);
    }

    /**
     * isGet
     * 是否GET调用
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public static function isGet()
    {
        if (empty($_SERVER['REQUEST_METHOD'])) {
            return false;
        }

        if ( 'get' === strtolower($_SERVER['REQUEST_METHOD'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * isPost
     * 是否POST调用
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public static function isPost()
    {
        if (empty($_SERVER['REQUEST_METHOD'])) {
            return false;
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 是否ajax调用
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public static function isAjax()
    {
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return false;
        }

        if ( 'xmlhttprequest' === strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * isFlash
     * 是否flash调用
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public static function isFlash()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        if ('shockwave flash' === strtolower($_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 得到用户浏览器详细信息
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public static function getUserBrowserByCap()
    {
        if ('' == ini_get('browscap')) {
            return false;
        }

        $info = get_browser(null, true);

        $browserInfo['os'] = $info['platform'];
        $browserInfo['browser'] = $info['browser'];
        $browserInfo['device_name'] = $info['device_name'];
        $browserInfo['device_maker'] = $info['device_maker'];
        $browserInfo['version'] = $info['version'];
        $browserInfo['max_version'] = $info['majorver'];
        $browserInfo['min_version'] = $info['minorver'];
        $browserInfo['cookie_support'] = $info['cookies'];
        $browserInfo['javaapplet_support'] = $info['javaapplets'];
        $browserInfo['javascript_support'] = $info['javascript'];
        $browserInfo['css_version'] = $info['cssversion'];

        return $browserInfo;
    }

    /**
     * 得到用户浏览器
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public static function getUserBrowser()
    {
         $agent = $_SERVER['HTTP_USER_AGENT'];

         $browser = '';
         $browserver = '';

         // TT Maxthon TheWorld Chrome Safari Firefox IE Opera
         // Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022; TheWorld)
         // 无版本信息
         if (false !== strpos($agent, 'TheWorld')) {
             $browser = 'TheWorld';
         }

         // Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; TencentTraveler 4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022)
         // 4.0
         elseif(false!==strpos($Agent, 'TencentTraveler'))
         {
             $temp = explode('TencentTraveler', $Agent);
             $part = $temp[1];
             $temp = explode(';', $part);
             $browserver = $temp[0];
             $browserver = preg_replace('/([^\d.]+)/', '', $browserver); // 去掉非'数字'或非'.'字符
             $browser = 'TT';
         }

         // Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022; MAXTHON 2.0)
         // 2.0
         elseif(false!==strpos($Agent, 'MAXTHON'))
         {
             $temp = explode('MAXTHON', $Agent);
             $browserver= $temp[1];
             $browserver = preg_replace('/([^\d.]+)/', '', $browserver); // 去掉非'数字'或非'.'字符
             $browser = 'Maxthon';
         }

         // Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Tride14:42 2009-10-8nt/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022)
         // 8.0
         elseif(false!==strpos($Agent, 'MSIE'))
         {
             $temp = explode('MSIE', $Agent);
             $browserver = $temp[1];
             $temp = explode(';', $browserver);
             $browserver = $temp[0];
             $browserver = preg_replace('/([^\d.]+)/', '', $browserver); // 去掉非'数字'或非'.'字符
             $browser = 'IE';
         }

         // Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/3.0.195.25 Safari/532.0
         // 3.0.195.25
         elseif(false!==strpos($Agent, 'Chrome'))
         {
             $temp = explode('Chrome', $Agent);
             $browserver = explode(' ', $temp[1]);
             $temp = $browserver[0];
             $browserver = $temp;
             $browserver = preg_replace('/([^\d.]+)/', '', $browserver); // 去掉非'数字'或非'.'字符
             $browser = 'Chrome';
         }

         // Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/531.9 (KHTML, like Gecko) Version/4.0.3 Safari/531.9.1
         // 4.0.3
         elseif(false!==strpos($Agent, 'Safari'))
         {
             $temp = explode('Version', $Agent);
             $browserver = $temp[1];
             $browserver = explode(' ', $browserver);
             $temp = $browserver[0];
             $browserver = $temp;
             $browserver = preg_replace('/([^\d.]+)/', '', $browserver); // 去掉非'数字'或非'.'字符
             $browser = 'Safari';
         }

         // Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3
         // 3.5.3
         elseif(false!==strpos($Agent, 'Firefox'))
         {
             $browserver = explode('Firefox', $Agent);
             $temp = $browserver[1];
             $browserver = $temp;
             $browserver = preg_replace('/([^\d.]+)/', '', $browserver); // 去掉非'数字'或非'.'字符
             $browser = 'Firefox';
         }

         // Opera/9.80 (Windows NT 5.1; U; zh-cn) Presto/2.2.15 Version/10.00
         // 10.00
         elseif(false!==strpos($Agent, 'Opera'))
         {
             $browserver = explode('Version', $Agent);
             $temp = $browserver[1];
             $browserver = $temp;
             $browserver = preg_replace('/([^\d.]+)/', '', $browserver); // 去掉非'数字'或非'.'字符
             $browser = 'Opera';
         }
         else
         {
             $browser = 'unknown';
             $browserver = 0;
         }

         $browserInfo['browser'] = $browser;
         $browserInfo['ver'] = $browserver;

         return $browserInfo;
    }

    /**
     * 得到用户操作系统
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public static function getUserOS()
    {
        $sys = $_SERVER['HTTP_USER_AGENT'];

        if (stripos($sys, "NT 6.1")) {
            $os = "Windows 7";
        } elseif(stripos($sys, "NT 6.0")) {
            $os = "Windows Vista";
        } elseif(stripos($sys, "NT 5.1")) {
            $os = "Windows XP";
        } elseif(stripos($sys, "NT 5.2")) {
            $os = "Windows Server 2003";
        } elseif(stripos($sys, "NT 5")) {
            $os = "Windows 2000";
        } elseif(stripos($sys, "NT 4.9")) {
            $os = "Windows ME";
        } elseif(stripos($sys, "NT 4")) {
            $os = "Windows NT 4.0";
        } elseif(stripos($sys, "98")) {
            $os = "Windows 98";
        } elseif(stripos($sys, "95")) {
            $os = "Windows 95";
        } elseif(stripos($sys, "Mac")) {
            $os = "Mac";
        } elseif(stripos($sys, "Linux")) {
            $os = "Linux";
        } elseif(stripos($sys, "Unix")) {
            $os = "Unix";
        } elseif(stripos($sys, "FreeBSD")) {
            $os = "FreeBSD";
        } elseif(stripos($sys, "SunOS")) {
            $os = "SunOS";
        } elseif(stripos($sys, "BeOS")) {
            $os = "BeOS";
        } elseif(stripos($sys, "OS/2")) {
            $os = "OS/2";
        } elseif(stripos($sys, "PC")) {
            $os = "Macintosh";
        } elseif(stripos($sys, "AIX")) {
            $os = "AIX";
        } else {
            $os = "unknown";
        }

        return $os;
    }

}

/**
 * 请求响应 
 *
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-06-19
 */
class Response
{
    /**
     * version
     *
     * @var mixed
     */
    public $version = null;

    /**
     * statusCode
     *
     * @var mixed
     */
    public $statusCode = null;

    /**
     * statusMessage
     *
     * @var mixed
     */
    public $statusMessage = null;

    /**
     * headers
     *
     * @var array
     */
    public $headers = [];

    /**
     * body
     *
     * @var mixed
     */
    public $body = null;

    /**
     * file
     *
     * @var mixed
     */
    public $file = null;

    /**
     * last_url
     *
     * @var mixed
     */
    public $last_url = null;

    /**
     * last_code
     *
     * @var mixed
     */
    public $last_code = null;

    /**
     * error_no
     *
     * @var mixed
     */
    public $error_no;

    /**
     * error_mess
     *
     * @var mixed
     */
    public $error_mess;

    /**
     * request
     *
     * @var mixed
     */
    public $request;


    /*
        检查是否发生错误
    */
    public function error()
    {
        if (($this->statusCode == 200) && empty($this->error_no)) {
            return false;
        }

        return true;
    }

    /**
     * errorMsg
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public function errorMsg()
    {
        if(empty($this->error_no)) {
            return $this->statusMessage;
        } else {
            return $this->error_mess;
        }
    }

    /**
     * errorCode
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public function errorCode()
    {
        $this->error_no;
    }

    /**
     * checkheader
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @param mixed $ctype
     * @return void
     */
    public function checkheader($ctype)
    {
        if (empty($ctype)) {
            return true;
        }

        if (!preg_match($ctype,$this->headers['Content-Type'])) {
            $this->error_no = 1001;
            $this->error_mess = "错误的文件类型";
            return false;
        }

        return true;
    }

    /**
    */
    /**
     * 将获取的数据保存到文件
     * 执行成功返回文件路径否则返回false
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @param mixed $file
     * @return void
     */
    public function save($file = null)
    {
        if ($this->error()) {
            return false;
        }

        if (empty($file)) {
            $file = $this->getTmpPath();
        }

        $this->mkPath($file);

        $fp = fopen($file, "w");

        if (!empty($fp)) {
            fwrite($fp, $this->body);
        } else {
            $this->error_no = 1000;
            $this->error_mess = "file:$file is not writable!";
            return false;
        }
        fclose($fp);
        $this->file = $file;
        return $file;
    }

    /**
     * 直接显示所获取的的数据
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    public function display()
    {
        if ($this->error()) {
            return false;
        }

        header("content-type: " .$this->headers['Content-Type']);
        echo $this->body;
        return true;
    }

    /**
     * getTmpPath
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @return void
     */
    private function getTmpPath()
    {
        $dstr = "";
        $m = "Za0YbXc1WdVe2UfTg3ShRiQ4jPk5Ol6NmM7nL8oKpJqIr9HsGtFuEvDwCxByAz";
        for ($i = 1;$i <= 8;$i++) {
            mt_srand( ( double )microtime() * 1000000 );
            $ta = mt_rand( 0, 61 );
            $dstr = $dstr . substr( $m, $ta, 1 );
        }

        if (strpos($this->headers['Content-Type'], ';')) {
            $ctype = explode(';', $this->headers['Content-Type']);
            $ext = substr($ctype[0],strrpos($ctype[0], '/')+1);
        } else {
            $ext = (substr($this->headers['Content-Type'],strrpos($this->headers['Content-Type'],"/")+1));
        }
        return rtrim(Config::item('UPLOAD_PATH'), '/') . "/temp/".$dstr.".".$ext;
    }

	/**
    * Creates the given directory path.  If the parent directories don't
    * already exist, they will be created, too.
    *
    * @param   string  $path       The full directory path to create.
    * @param   integer $mode       The permissions mode with which the
    *                              directories will be created.
    *
    * @return  True if the full path is successfully created or already
    *          exists.
    *
    */
	private function mkPath($path, $mode = 0755){
		static $depth = 0;

		/* Guard against potentially infinite recursion. */
		if ($depth++ > 25) {
			trigger_error("mkpath(): Maximum recursion depth (25) exceeded",
			E_USER_WARNING);
			return false;
		}

		/* We're only interested in the directory component of the path. */
		$path = dirname($path);


		/* If the directory already exists, return success immediately. */
		if (is_dir($path)) {
			$depth = 0;
			return true;
		}

		/*
		* In order to understand recursion, you must first understand
		* recursion ...
		*/
		if ($this->mkPath($path, $mode) === false) {
			return false;
		}

		return @mkdir($path, $mode);
	}
}

/**
 * CURL - Core 
 *
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-06-19
 */
class HttpClient{

    /**
     * curl - get
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @param mixed $httpRequest
     * @return void
     */
    static function get(&$httpRequest)
    {
        $url = HttpClient::getURL($httpRequest);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($httpRequest->cookies != null) {
            curl_setopt($ch, CURLOPT_COOKIE,  HttpClient::getCookies($httpRequest));
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $httpRequest->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $httpRequest->timeout);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

        if ($httpRequest->headers != null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER,  HttpClient::getHeaders($httpRequest));
        }

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 0);

        HttpClient::setExtraCurlOptions($httpRequest, $ch);

        $response = curl_exec($ch);
        $error_no =  curl_errno($ch);
        $error_mess = curl_error($ch);
        $last_url   = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
        $last_code  = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!empty($error_no)) {
            $httpResponse = new Response();
            $httpResponse->error_no = $error_no;
            $httpResponse->error_mess = $error_mess;
        } else {
            $httpResponse = HttpClient::parseResponse($response);
        }
        if ($httpRequest->followRedirect === true && $httpRequest->numRedirect < $httpRequest->maxRedirect) {
            if (array_key_exists('Location',$httpResponse->headers)) {
                $httpRequest->url = $httpResponse->headers['Location'];
                $httpRequest->parameters = null;
                $httpRequest->numRedirect++;
                $httpResponse =& HttpClient::get($httpRequest);
            }
        }

        $httpResponse->last_url = $last_url;
        $httpResponse->last_code = $last_code;
        $httpResponse->request = &$httpRequest;

        return $httpResponse;
    }

    /**
     * curl - post
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @param mixed $httpRequest
     * @return void
     */
    static function post(&$httpRequest)
    {
        $url = $httpRequest->url;
        $url = HttpClient::getURL($httpRequest);

        if ($httpRequest->body != null) {
            $body =& $httpRequest->body;
        } else {
            $body = HttpClient::buildQuery($httpRequest->parameters);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($httpRequest->cookies != null) {
            curl_setopt($ch, CURLOPT_COOKIE,  HttpClient::getCookies($httpRequest));
        }
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $httpRequest->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $httpRequest->timeout);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

        if ($httpRequest->headers != null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER,  HttpClient::getHeaders($httpRequest));
        }

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        # curl_setopt(): Disabling safe uploads is no longer supported after php 7
	    if (version_compare(PHP_VERSION, '7', '<')) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        }
        HttpClient::setExtraCurlOptions($httpRequest, $ch);
        $response = curl_exec($ch);

        $error_no =  curl_errno($ch);
        $error_mess = curl_error($ch);
        $last_url   = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
        $last_code  = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!empty($error_no)) {
            $httpResponse = new Response();
            $httpResponse->error_no = $error_no;
            $httpResponse->error_mess = $error_mess;
        } else {
            $httpResponse = HttpClient::parseResponse($response);
        }

        $httpResponse->last_url  = $last_url;
        $httpResponse->last_code = $last_code;
        $httpResponse->request = &$httpRequest;
        return $httpResponse;
    }

    /**
     * 构造查询串
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @param mixed $arr
     * @return void
     */
    static function buildQuery($arr)
    {
        if ($arr == null) {
            return false;
        }

        $url = '';
        $init = false;
        foreach ($arr as $key=>$value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $url .= urlencode($key).'='.urlencode($val).'&';
                }
            } else {
                $url .= urlencode($key).'='.urlencode($value).'&';
            }
        }

        return rtrim($url,'&');
    }

    /**
     * 拼接url
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @param mixed $httpRequest
     * @return void
     */
    static function getURL(&$httpRequest)
    {
        $query = HttpClient::buildQuery($httpRequest->parameters);
        if ($query == false) {
            return $httpRequest->url;
        }
        if (strpos($httpRequest->url,'?')==false) {
            return $httpRequest->url.'?'.$query;
        } else {
            return $httpRequest->url.'&'.$query;
        }
    }

    /**
     * getHeaders
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @param mixed $httpRequest
     * @return void
     */
    static function getHeaders(&$httpRequest)
    {
        $headers = [];

        foreach ($httpRequest->headers as $key=>$val) {
            if (is_string($key)) {
                $headers[] = $key.': '.$val;
            } else {
                $headers[] = $val;
            }
        }

        return $headers;
    }

    /**
     * getCookies
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @param mixed $httpRequest
     * @return void
     */
    static function getCookies(&$httpRequest)
    {
        if (is_string($httpRequest->cookies)) {
            return $httpRequest->cookies;
        }

        $cookies = "";
        foreach ($httpRequest->cookies as $key=>$val) {
            if (is_array($val)) {
                foreach ($val as $vkey=>$vval) {
                    $cookies .= ';'.$key.'['.$vkey.']'.'='.urlencode($vval);
                }
            } else {
                if (strpos($val,'=') === false) {
                    $cookies .= ';'.$key.'='.urlencode($val);
                } else {
                    $cookies .= ";".urlencode($val);
                }
            }
        }

        return $cookies;
    }

    /**
     * setExtraCurlOptions
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @param mixed $httpRequest
     * @param mixed $ch
     * @return void
     */
    static function setExtraCurlOptions(&$httpRequest, &$ch){
        if (!is_array($httpRequest->curlOpts)) {
            return;
        }

        foreach ($httpRequest->curlOpts as $key=>$value) {
            curl_setopt($ch, $key, $value);
        }
    }

    /**
     * parseResponse
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-19
     *
     * @param mixed $response
     * @return void
     */
    static function parseResponse(&$response)
    {
        $httpResponse = new Response();

        $parts = preg_split('/\r\n\r\n/',$response,2);
        $nparts = count($parts);
        $headerLines = $nparts>0 ? $parts[0] : null;
        $contentLines = $nparts>1 ? $parts[1] : null;
        while (preg_match('/^HTTP/',$contentLines)) {
            $parts = preg_split('/\r\n\r\n/',$contentLines,2);
            $nparts = count($parts);
            $headerLines = $nparts>0 ? $parts[0] : null;
            $contentLines = $nparts>1 ? $parts[1] : null;
        }
        $httpResponse->body =& $contentLines;
        $httpResponse->headers = [];

        $lines = explode("\r\n",$headerLines);
        if ($lines) {
            foreach($lines as $line) {
                $parts = array();
                if (preg_match('/^([a-zA-Z -]+): +(.*)$/',$line,$parts)) {
                    if (isset($httpResponse->headers[$parts[1]])) {
                        if (is_array($httpResponse->headers[$parts[1]])) {
                            $httpResponse->headers[$parts[1]][] = $parts[2];
                        } else {
                            $preExisting = $httpResponse->headers[$parts[1]];
                            $httpResponse->headers[$parts[1]]= array($preExisting,$parts[2]);
                        }
                    } else {
                        $httpResponse->headers[$parts[1]]=$parts[2];
                    }
                } elseif ( preg_match('/^HTTP/',$line) ) {
                    $parts = preg_split('/\s+/',$line,3);
                    $nparts = count($parts);
                    if ($nparts > 0) {
                        $httpResponse->version = $parts[0];
                    }
                    if ($nparts > 1) {
                        $httpResponse->statusCode = $parts[1];
                    }
                    if ($nparts > 2) {
                        $httpResponse->statusMessage = $parts[2];
                    }
                }
            }
        }

        return $httpResponse;
    }
}
