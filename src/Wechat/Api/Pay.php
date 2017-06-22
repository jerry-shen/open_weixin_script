<?php
namespace Wechat\Api;
/**
* @description wx pay 微信支付相关 - 需改写
* @author jlzhang
* @email  779581051@qq.com
* @version 1.0
* @createDate 2015/2/3
* @modify
*/
class Pay extends Core
{
	//参数
	private $_params     = array();
    private $_appid      = '';
    private $_mch_id     = '';
    private $_mch_Key    = '';
    private $_notify_url = '';
    private $_ssl_cert   = '';
    private $_ssl_key    = '';
	
    //wx pay url 
    const WX_PAY_URL = 'weixin://wxpay/bizpayurl';
	// 查询订单
	const ORDER_QUERY_URL = 'https://api.mch.weixin.qq.com/pay/orderquery';
	// 查询退款
	const REFUND_QUERY_URL = 'https://api.mch.weixin.qq.com/pay/refundquery';
	// 下载对账单
	const DOWNLOAD_BILL_URL = 'https://api.mch.weixin.qq.com/pay/downloadbill';
	
    public function __construct()
    {
		parent::__construct ();
		$this->run();
	}
	
	//设置 wx base 参数 需要提前在conf中设置
    private function run($appId, $mchId, $mchKey, $payCert, $payKey, $notifyUrl)
    {
		$this->_appid       = $appId;
		$this->_mch_id      = $mchId;
		$this->_notify_url  = $notifyUrl;
		$this->_mch_Key     = $mchKey;
		$this->_ssl_cert    = $payCert;
		$this->_ssl_key     = $payKey;
	}
	
	/**
	 * 统一下单
	 * wiki url:http://pay.weixin.qq.com/wiki/doc/api/index.php?chapter=9_1
	 * @param array $params
	 * @return array $result
	*/
    public function unifiedOrder($data)
    {
		$this->setParam('notify_url', $this->_notify_url);
		return $this->getData($data, self::UNIFIED_ORDER_URL);
	}

	/**
	 * 静态二维码支付url 暂留 以后待处理
	*/
    public function getStaticUrl($productId)
    {
        if (empty($productId)) {
            return false;
        }

        $this->resetParams();
        $staticParams = array( 
                            'product_id' => $product_id,
                            'appid' => $this->_appid,
                            'time_stamp' => time(),
                            'nonce_str' => $this->createNonceStr(32),
                            'mch_id' => $this->_mch_id);
        $staticParams['sign'] =  $this->createSign($staticParams);

        return self::WX_PAY_URL."?".$this->formatParams($staticParams);
	}

	/**
	 * 查询订单 NATIVE 时返回动态二维码
	 * wiki url:http://pay.weixin.qq.com/wiki/doc/api/index.php?chapter=9_2
	 * @param array $url
	 * @return array $result
	*/
    public function orderQuery($data)
    {
		return $this->getData($data, self::ORDER_QUERY_URL);
	}

	/**
	 * 查询退款
	 * wiki url:http://pay.weixin.qq.com/wiki/doc/api/index.php?chapter=9_5
	 * @param array $url
	 * @return array $result
	*/
    public function refundQuery($data)
    {
		return $this->getData($data, self::REFUND_QUERY_URL);
	}

	/**
	 * 下载对账单
	 * wiki url:http://pay.weixin.qq.com/wiki/doc/api/index.php?chapter=9_6
	 * @param array $url
	 * @return array $result
	 */
    public function downloadBill($data)
    {
		return $this->getData($data, self::DOWNLOAD_BILL_URL);
	}
	
	/**
	 * 公共获取api结果
	*/
    protected function getData($params, $url, $curlOpts = array())
    {
		$this->setParam('appid', $this->_appid);
		$this->setParam('mch_id', $this->_mch_id);	
		//随机字符串 Y
		$this->setParam('nonce_str', $this->createNonceStr(32));

		if (!is_array($params) || count($params) < 1) {
			$this->setError('请先设置参数');
			return false;
		}
		foreach ($params as $k=>$v) {
			$this->setParam($k, $v);
		}
		//签名 Y
		$this->setParam('sign', $this->createSign($this->getParams()));

		$request = $this->request();
		$request->url  = $url;
		$request->body = Xml::arrayToXml( $this->getParams() );
		if ($curlOpts) {
			$request->curlOpts = $curlOpts;
		}
		$response = $request->post ();
		
		//如果不是xml个是数据直接返回
		$xml_parser = xml_parser_create();
		if (!xml_parse($xml_parser,$response->body,true)) {
			xml_parser_free($xml_parser);
			return $response->body;
		}
		//解析xml
		$return = Xml::xmlToArray( $response->body);

		//重置params
		$this->resetParams();
		
		if (! isset ( $return ['return_code'] ) || $return ['return_code'] == 'SUCCESS') {
			return $return;
		} else {
			$this->setError ( $return );
			return false;
		}
	}

	//生成签名
	private function createSign($conf, $isMchKey = true)
	{
		if(!is_array($conf)){
			return FALSE;
		}
		
		$params = array();
		foreach ($conf as $k => $v){
			if(!empty($v)){
				$params[$k] = $v;
			}
		}
		//签名步骤一：按字典序排序参数
		ksort($params);
		$str = $this->formatParams($params);
		//签名步骤二：在string后加入KEY
		if($isMchKey){
	   		$str = $str."&key=".$this->_mch_Key;
		}
        //签名步骤三：MD5加密
		$str = md5($str);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($str);
		return $result;
	}
	
	//格式化参数，签名过程需要使用
	private function formatParams($params)
	{
		$buff = "";
		ksort($params);
		foreach ($params as $k => $v) {
			$buff .= $k . "=" . $v . "&";
		}
		$result = '';
		if (strlen($buff) > 0) {
			$result = substr($buff, 0, strlen($buff)-1);
		}
		return $result;
	}

	//设置请求参数
	public function setParam($name, $val)
	{
		$this->_params[$this->trimStr($name)] = $this->trimStr($val);
	}

	//去除空格
	private function trimStr($val)
	{
		$ret = null;
		if (null != $val) {
			$ret = trim($val);
			if (strlen($ret) == 0) {
				$ret = null;
			}
		}
		return $ret;
	}
	//获取参数
    public function getParams($key = false)
    {
		return $key && isset($this->_params[$key])? $this->_params[$key] : $this->_params;
	}

	//重置 参数
    public function resetParams()
    {
		$this->_params = array();
	}
	//tools fun -e
}
