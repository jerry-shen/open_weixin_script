<?php
namespace Wechat\Api;
/**
* @description 语义理解接口 
* @author shf1986@gmail.com
* @version 1.0
* @createDate 2015/1/7
* @modify
*/
class Semantic extends Core
{
    //长链接转成短链接
    const SEMANTIC_SEARCH_URL = '/semantic/semproxy/search';

    public function __construct()
    {
        parent::__construct();
    }

    /**
    * 语义理解接口
    * @param array $data
    * @return boolean|array
    * array(
    *   'errcode' => Int,//0
    *   'query'   => String,//用户的输入字符串,"我是中国人"
    *   'type'    => String,//服务的全局类型id，详见协议文档中垂直服务协议定义
    *   'semantic'=> Object,//语义理解后的结构化标识，各服务不同
    *   'result'  => Array,
    *   'answer'  => String,
    *   'text'    => String
    * )
    **/
    public function search($data)
    {
        return $this->postData($data, self::SEMANTIC_SEARCH_URL);
    }
}
?>
