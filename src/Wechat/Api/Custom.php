<?php
namespace Wechat\Api;

/**
* @description 客服相关
* @author shf1986@gmail.com
* @version 1.0
* @createDate 2015/1/7
* @modify 2016-03-23
* @error code
*   65400 - API不可用，即没有开通/升级到新版客服功能
*   65401 - 无效客服帐号
*   65403 - 客服昵称不合法
*   65404 - 客服帐号不合法
*   65405 - 帐号数目已达到上限，不能继续添加
*   65406 - 已经存在的客服帐号
*   65407 - 邀请对象已经是该公众号客服
*   65408 - 本公众号已经有一个邀请给该微信
*   65409 - 无效的微信号
*   65410 - 邀请对象绑定公众号客服数达到上限（目前每个微信号可以绑定5个公众号客服帐号）
*   65411 - 该帐号已经有一个等待确认的邀请，不能重复邀请
*   65412 - 该帐号已经绑定微信号，不能进行邀请
*	65413 - 不存在对应用户的会话信息
*   65414 - 客户正在被其他客服接待
*   65415 - 指定的客服不在线
*   65416 - 查询参数不合法
*   65417 - 查询时间段超出限制
*/
class Custom extends Core
{
    //获取所有客服账号
    const LIST_KF_URL   = '/cgi-bin/customservice/getkflist';

    //发送客服消息
    const SEND_KF_URL   = '/cgi-bin/message/custom/send';

    //获取客服聊天记录
    const GET_RECORD_URL= '/customservice/msgrecord/getmsglist';

    //获取在线客服
    const ONLINE_KF_URL = '/cgi-bin/customservice/getonlinekflist';

    public function __construct()
    {
        parent::__construct();            
    }

    /**
    * 发送客服消息
    * 如果需要以某个客服帐号来发消息，则需在JSON数据包的后半部分加入customservice参数
    * array(
    *    "openid" => "OPENID",
    *    "msgtype" => "text",
    *    "text" => array(
    *         "content"=>"Hello World"
    *    ),
    *    "customservice" => array(
    *         "kfaccount" => "test1@kftest"
    *    )
    * )
    * 如果通过上面的数组方式则需要$content为urlencode(json_encode($content))做string来传递
    * 如果想按照数组传递则传递如下内容 如果指定客服则增加account键值
    * added description by aoniboy <mingzhen.sun@eub-inc.com> 
    * array(
    *    "openid" => "OPENID",
    *    "msgtype" => "text",
    *    "content"=>"Hello World",
    *    "account" => "test1@kftest"
    * )
    * @param array|string $content
    * return boolean
    **/
    public function sendServiceMessage($content)
    {
        $request = $this->request();
        
        $request->url = self::SEND_KF_URL;
        $request->parameters = array(
            'access_token' => $this->token,
        );
        $request->body = $content;
        $response = $request->post();
        $return = json_decode($response->body, true);
        if (0 == $return['errcode']) {
            return true;                
        } else {
            if (in_array($return['errcode'],$this->refresh_token_errno)) {
                $this->refreshAccessToken();
                return $this->sendServiceMessage($content);
            }
            $this->setError($return);
            return false;                
        }
    }
    
    /**
    * 获取所有客服账号
    * @param array $data
    * @return array $result
    * array(
    *   'kf_account'    => 'name@test',//客服名称 + '@' + 公众账号
    *   'kf_headimgurl' => '客服头像',
    *   'kf_nick'       => '客服昵称',
    *   'kf_id'         => '客服编号',
    *   'kf_wx'         => '如果客服帐号已绑定了客服人员微信号，则此处显示微信号',
    *   'invite_wx'     => '如果客服帐号尚未绑定微信号，但是已经发起了一个绑定邀请，则此处显示绑定邀请的微信号',
    *   'invite_expire_time' => '如果客服帐号尚未绑定微信号，但是已经发起过一个绑定邀请，邀请的过期时间，为unix 时间戳',
    *	'invite_status' => '邀请的状态，有等待确认“waiting”，被拒绝“rejected”，过期“expired”'
    * )
    * @return boolean|array
    **/
    public function kfList()
    {
        return $this->getData(array(), self::LIST_KF_URL, 'kf_list');
    }

    /**
    * 获取所有在线客服账号
    * @param array $data
    * array(
    *   'kf_account'    => 'name@test',//客服名称 + '@' + 公众账号
    *   'status'        => '客服在线状态，目前为：1、web 在线',
    *   'kf_id'         => '客服编号',
    *   'accepted_case' => '客服当前正在接待的会话数',
    * )
    * @return boolean|array
    **/
    public function onlineKfList() {
        return $this->getData(array(), self::ONLINE_KF_URL, 'kf_online_list');
    }

    /**
     * 获取客服聊天记录接口 - 客服功能会在云端保存30天的聊天记录
     * @param string $openid - 空则是全部
     * @param int $starttime - unix时间戳
     * @param int $endtime - unix时间戳
     * @param int $pageindex - 从1开始 - 已废弃
     * @param int $pagesize - 最多50 － 已废弃
     * @param int $msgid - 消息id顺序从小到大，从1开始
     * @param int $number - 每次获取条数，最多10000条
     * @return boolean|array
     **/
    public function getCustomRecord($starttime, $endtime, $msgid = 1 ,$number = 10000)
    {
        $data = array(
            "starttime" => $starttime,
            "endtime"   => $endtime,
            "msgid"     => $msgid,
            "number"    => $number
        );
        return $this->postData($data, self::GET_RECORD_URL);
    }
}
?>
