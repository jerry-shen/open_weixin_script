<?php
namespace Wechat\Api;

/**
* @description 用户相关
* @author shf1986@gmail.com
* @version 1.0
* @createDate 2015/1/7
* @modify
*/
class User extends Core {
    //获取用户基本信息
    const USER_INFO_URL     = '/cgi-bin/user/info';

    //批量获取用户基本信息,最多100个
    const BATCH_USER_INFO_URL     = '/cgi-bin/user/info/batchget';

    //修改用户的备注名
    const USER_MARK_URL     = '/cgi-bin/user/info/updateremark';

    //获取微信用户列表
    const USER_LIST_URL     = '/cgi-bin/user/get';

    //sns获取用户信息
    const SNS_USER_INFO_URL = '/sns/userinfo';        

    //黑名单列表
    const BLACK_USER_LIST_URL = '/tags/members/getblacklist';

    //拉黑用户
    const BLACK_USER_URL = '/tags/members/batchblacklist';

    //取消拉黑用户
    const UNBLACK_USER_URL = '/tags/members/batchunblacklist';

    public function __construct()
    {
        parent::__construct();
    }

    /**
    * 获取用户信息
    * @param $openid
    * @param $lang
    * @return array
     {
        "subscribe":用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。,
        "openid":用户的标识,
        "nickname":用户的昵称,
        "sex":用户的性别，值为1时是男性，值为2时是女性，值为0时是未知,
        "language":用户的语言，简体中文为zh_CN,
        "city":用户所在城市,
        "province":用户所在省份,
        "country":用户所在国家,
        "headimgurl":用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。,
        "subscribe_time":用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间,
        "unionid":只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。,
        "remark":公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注,
        "groupid":用户所在的分组ID
        "tagid_list":标签列表
     }
    **/
    public function getUserInfo($openid, $lang = 'zh_CN')
    {
        $isFailed = false;
        $times = 0;
        $data = array(
            'openid' => $openid,
            'lang'   => $lang		
        );

        do{
            //获取token
            if(!$this->refreshAccessToken()) {
                $isFailed = true;
                $times++;
            }
            if($result = $this->getData($data, self::USER_INFO_URL)){
                return $result;
            } else {
                $times++;
                $isFailed = true;
            }
        } while($isFailed && $times < 4);

        return false;
    }

    /**
    * 批量获取用户基本，最多100个
    * @param $openids - 二维数组 
     {
        "user_list": [
            {
                "openid":用户的标识,
                "lang":国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语，默认为zh-CN
            },
            {
                "openid":,
                "lang":
            },
        ]
     }
    * @return array
     {
        "user_info_list":[
            {
            //结构同单个用户信息	
            },
            
        ]
     }
    **/
    public function getBatchUserInfo($openids = array())
    {
        $data = array(
            'user_list' => $openids
        );
        return $this->postData($data, self::BATCH_USER_INFO_URL, 'user_info_list');
    }

    /**
    *网页授权获取用户信息
    **/
    public function getSnsUserInfo($token, $openid, $lang = 'zh_CN')
    {
        $data = array(
            'access_token' => $token,
            'openid' => $openid,
            'lang'   => $lang
        );
        return $this->getData($data, self::SNS_USER_INFO_URL);
    }

    /**
    * 修改用户的备注名
    * @param $openid 
    * @param $mark - 长度必须小于30字符 
    * @return boolean
    **/
    public function updateUserMark($openid, $mark)
    {
        $data = array(
            'openid' => $openid,
            'remark' => urlencode($mark)
        );    
        return $this->postData($data, self::USER_MARK_URL);
    }

    /**
    * 获取用户列表
    * 一次拉取调用最多拉取10000个关注者的OpenID
    * @param $nextid - string
    * @return array
     {
        "total": 关注该公众账号的总用户数,
        "count": 拉取的OPENID个数，最大值为10000,
        "data": 列表数据，OPENID的列表,
            {
                "openid":[
                    "OPENID1",
                    "OPENID2",
                    ...,
                    "OPENID10000"
                ]
            }
        "next_openid": 拉取列表的最后一个用户的OPENID
     }
    **/
    public function getUserList($nextid = '')
    {
        if($nextid) {
            $data = array(
                'next_openid' => $nextid
            );
        } else {
            $data = array(
            );
        }
        return $this->getData($data, self::USER_LIST_URL);
    }

    /**
    * 获取黑名单列表 每次最多10000条，如果超过10000，用next_openid的值
    * @param $nextOpenid - string
    * @return $openids
    **/
    public function getBlackUserList($nextOpenid = '')
    {
        if ($nextOpenid) {
            $data = array(
                'next_openid' => $nextOpenid        
            );
        } else {
            $data = array();
        }
        return $this->postData($data, self::BLACK_USER_LIST_URL);
    }

    /**
    * 拉黑用户 - 每次最多20个
    * @param $openids - array
    * @return boolean
    **/
    public function blackUsers($openids)
    {
        $data = array(
            'openid_list' => $openids        
        );
        return $this->postData($data, self::BLACK_USER_URL);
    }

    /**
    * 取消 拉黑用户 - 每次最多20个
    * @param $openids - array
    * @return boolean
    **/
    public function unblackUsers($openids)
    {
        $data = array(
            'openid_list' => $openids        
        );
        return $this->postData($data, self::UNBLACK_USER_URL);
    }
}
?>
