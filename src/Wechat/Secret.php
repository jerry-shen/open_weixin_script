<?php
namespace Wechat;

/**
 * @description 微信消息加密解密类  基于微信提供的PKCS7Encoder算法
 * @author  junliang.zhang
 * @email   779581051@eub-inc.com
 * @version 2014/11/25
 * @modify
 */

class Secret
{

	//微信 AppId
	protected  $_token;

	//微信 AppId
	protected  $_appId;

	//消息加解密密钥
	protected  $_encodingAesKey;

    //PKCS7Encoders 算法 size
	private static $block_size = 32;

	/**
	 * _result
	 *
	 * @var mixed
	 */
	protected  $_result = [];

	/**
	 * __construct
	 * @author Jerry Shen <haifei.shen@eub-inc.com>
	 * @version 2017-06-19
	 *
	 * @param mixed $token
	 * @param mixed $appId
	 * @param mixed $encodingAesKey
	 * @return void
	 */
    public function __construct($token, $appId, $encodingAesKey)
    {
        $this->_token = $token;
        $this->_appId = $appId;
        $this->_encodingAesKey = base64_decode ( $encodingAesKey . "=" );
	}

	/**
	 * 对明文进行加密
	 * @param string $text 需要加密的明文
	 * @return string 加密后的密文
	 */
    public function encrypt($text)
    {
		try {
			// 获得16位随机字符串，填充到明文之前
			$random = $this->getRandomStr ();
			$text = $random . pack ( "N", strlen ( $text ) ) . $text . $this->_appId;
			// 网络字节序
			$size = mcrypt_get_block_size ( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC );
			$module = mcrypt_module_open ( MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '' );
			$iv = substr ( $this->_encodingAesKey, 0, 16 );
			// 使用自定义的填充方式对明文进行补位填充
			$text = $this->encode ( $text );
			mcrypt_generic_init ( $module, $this->_encodingAesKey, $iv );
			// 加密
			$encrypted = mcrypt_generic ( $module, $text );
			mcrypt_generic_deinit ( $module );
			mcrypt_module_close ( $module );

			// 使用BASE64对加密后的字符串进行编码
			$encrypted = base64_encode ( $encrypted );

			//处理加密字符串为 xml
			$timeStamp = time();

			$nonce     = rand(100000000,999999999);

			//生成安全签名
			$array = $this->getSHA1($this->_token, $timeStamp, $nonce, $encrypted);
			if($array['error'] == 0){
				$signature = $array['data'];
			}else{
				return $array;
			}

			//生成发送的xml
			$encryptMsg = $this->generate($encrypted, $signature, $timeStamp, $nonce);

			return $this->getErrorCode('OK',$encryptMsg);
		} catch ( Exception $e ) {
			return $this->getErrorCode('EncryptAESError');
		}
	}

	/**
	 * 对密文进行解密
	 * @param array $params
	 * //@param string $encrypted 需要解密的密文
	 * @return string 解密得到的明文
	 */
    public function decrypt($params)
    {
		if(!isset($params['encrypt'])){
			return $params;
		}

		$encrypted = $params['encrypt'];

		try {
			// 使用BASE64对需要解密的字符串进行解码
			$ciphertext_dec = base64_decode ( $encrypted );
			$module = mcrypt_module_open ( MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '' );
			$iv = substr ( $this->_encodingAesKey, 0, 16 );
			mcrypt_generic_init ( $module, $this->_encodingAesKey, $iv );

			// 解密
			$decrypted = mdecrypt_generic ( $module, $ciphertext_dec );
			mcrypt_generic_deinit ( $module );
			mcrypt_module_close ( $module );
		} catch ( Exception $e ) {
			return $this->getErrorCode('DecryptAESError');
		}

		try {
			// 去除补位字符
			$result = $this->decode ( $decrypted );
			// 去除16位随机字符串,网络字节序和AppId
			if (strlen ( $result ) < 16)
				return "";
			$content = substr ( $result, 16, strlen ( $result ) );
			$len_list = unpack ( "N", substr ( $content, 0, 4 ) );
			$xml_len = $len_list [1];
			$xml_content = substr ( $content, 4, $xml_len );
			$from_appid = substr ( $content, $xml_len + 4 );
			if ($from_appid != $this->_appId){
				return $this->getErrorCode('ValidateAppidError');
			}else{
				$xml_content = (array) simplexml_load_string($xml_content, 'SimpleXMLElement', LIBXML_NOCDATA);
				//将数组键名转换为小写，提高健壮性，减少因大小写不同而出现的问题
				$xml_array  = array_change_key_case($xml_content, CASE_LOWER);
				$result     = array_merge($xml_array,$params);
				return $this->getErrorCode('OK',$result);
			}
		} catch ( Exception $e ) {
			return $this->getErrorCode('IllegalBuffer');
		}
	}

	/**
	 * 随机生成16位字符串
	 *
	 * @return string 生成的字符串
	 */
    private function getRandomStr()
    {
		$str = "";
		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen ( $str_pol ) - 1;
		for($i = 0; $i < 16; $i ++) {
			$str .= $str_pol [mt_rand ( 0, $max )];
		}
		return $str;
	}

	/**
	 * 返回错误状态机结果
	 * @param  string $ac
	 * @param  array/xml  $params
	 * @return array  $result
	 */
    public function getErrorCode($ac,$params = false)
    {
		$result = array();
		switch ($ac){
			case 'OK':
				$result['error'] =  0;
				$result['data']  =  $params;
				break;
			case 'ValidateSignatureError':
				$result['error'] = 40001;
				$result['msg']  =  '签名验证错误';
				break;
			case 'ParseXmlError':
				$result['error'] = 40002;
				$result['msg']  =  'xml解析失败';
				break;
			case 'ComputeSignatureError':
				$result['error'] = 40003;
				$result['msg']  =  'sha加密生成签名失败';
				break;
			case 'IllegalAesKey':
				$result['error'] = 40004;
				$result['msg']  =  'encodingAesKey 非法';
				break;
			case 'ValidateAppidError':
				$result['error'] = 40005;
				$result['msg']  =  'appid 校验错误';
				break;
			case 'EncryptAESError':
				$result['error'] = 40006;
				$result['msg']  =  'aes 加密失败';
				break;
			case 'DecryptAESError':
				$result['error'] = 40007;
				$result['msg']  =  'aes 解密失败';
				break;
			case 'IllegalBuffer':
				$result['error'] = 40008;
				$result['msg']  =  '解密后得到的buffer非法';
				break;
			case 'EncodeBase64Error':
				$result['error'] = 40009;
				$result['msg']  =  'base64加密失败';
				break;
			case 'DecodeBase64Error':
				$result['error'] = 40010;
				$result['msg']  =  'base64解密失败';
				break;
			case 'GenReturnXmlError':
				$result['error'] = 40011;
				$result['msg']  =  '生成xml失败';
				break;
			default:
				$result['error'] = 50000;
				$result['msg']  =  '未知错误';
				break;
		}
		return $result;
	}

	/**
	 * PKCS7算法-start
	 */
	/**
	 * 对需要加密的明文进行填充补位
	 * @param $text 需要进行填充补位操作的明文
	 * @return 补齐明文字符串
	 */
    private function encode($text)
    {
		$block_size = self::$block_size;
		$text_length = strlen ( $text );
		// 计算需要填充的位数
		$amount_to_pad = self::$block_size - ($text_length % self::$block_size);
		if ($amount_to_pad == 0) {
			$amount_to_pad = self::block_size;
		}
		// 获得补位所用的字符
		$pad_chr = chr ( $amount_to_pad );
		$tmp = "";
		for($index = 0; $index < $amount_to_pad; $index ++) {
			$tmp .= $pad_chr;
		}
		return $text . $tmp;
	}

	/**
	 * 对解密后的明文进行补位删除
	 *
	 * @param  decrypted 解密后的明文
	 * @return 删除填充补位后的明文
	 */
    private function decode($text)
    {
		$pad = ord ( substr ( $text, - 1 ) );
		if ($pad < 1 || $pad > 32) {
			$pad = 0;
		}
		return substr ( $text, 0, (strlen ( $text ) - $pad) );
	}

	/**
	 * PKCS7算法-end
	 */


	/**
	 * 生成xml消息
	 * @param string $encrypt 加密后的消息密文
	 * @param string $signature 安全签名
	 * @param string $timestamp 时间戳
	 * @param string $nonce 随机字符串
	 */
	private function generate($encrypt, $signature, $timestamp, $nonce)
	{
		$format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";
		return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
	}

	/**
	 * 用SHA1算法生成安全签名
	 * @param string $token 票据
	 * @param string $timestamp 时间戳
	 * @param string $nonce 随机字符串
	 * @param string $encrypt 密文消息
	 */
	private function getSHA1($token, $timestamp, $nonce, $encrypt_msg)
	{
		//排序
		try {
			$array = array($encrypt_msg, $token, $timestamp, $nonce);
			sort($array, SORT_STRING);
			$str = implode($array);
			return $this->getErrorCode('OK', sha1($str));
		} catch (Exception $e) {
			return $this->getErrorCode('ComputeSignatureError');
		}
	}

    /**
    * 验证签名
    * @param string - $key 私钥
    * @return boolean
    **/
    public function validate($signature, $timestamp, $nonce)
    {
        if (empty($signature) || empty($timestamp) || empty($nonce)) {
            return false;
        }
        $tmpArr = array($this->_token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        return sha1(implode($tmpArr)) === $signature;
    }
}
