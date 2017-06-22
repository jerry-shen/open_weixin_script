<?php
namespace Wechat\Api;

/**
* @description 用户标签
* @author shf1986@gmail.com
* @version 1.0
* @createDate 2016/04/29
* @modify
* 一个公众账号，最多支持创建100个标签
*/
class Tag extends Core {
    //创建用户标签
    const CREATE_TAG = '/cgi-bin/tags/create';

    //删除用户标签
    const DEL_TAG = '/cgi-bin/tags/delete';

    //获取所有标签
    const TAG_LIST   = '/cgi-bin/tags/get';

    //查询用户所在标签
    const USER_TAG   = '/cgi-bin/tags/getidlist';

    //修改标签名称
    const MODIFY_TAG = '/cgi-bin/tags/update';

    //获取标签下粉丝列表
    const TAG_USER_LIST = '/cgi-bin/user/tag/get';

    //批量为用户打标签
    const BATCH_TAG = '/cgi-bin/tags/members/batchtagging';

    //批量取消用户标签
    const BATCH_UNTAG = '/cgi-bin/tags/members/batchuntagging';

    public function __construct()
    {
        parent::__construct();
    }

    /**
    * 创建标签
    * @param $name - string - 30个字符以内,UTF8编码
    * @return $tagid - boolean|int
    **/
    public function createUserTag($name)
    {
        $data = array(
            'tag'=>array(
                'name'=>urlencode($name)
            )
        );
        return $this->postData($data, self::CREATE_TAG, 'tag');
    }

    /**
    * 修改标签名称
    * @param $gid int - 原标签
    * @param $name string - 新标签名称
    * @return boolean
    **/
    public function modifyUserTag($gid, $name)
    {
        $data = array(
            'tag' => array(
                'id' => $gid,
                'name' => urlencode($name)
            )
        );
        return $this->postData($data, self::MODIFY_TAG);
    }

    /**
    * 删除标签 - 标签删除后，所有该标签内的用户自动进入默认标签，只能删除小于10000的用户标签
    * @param $tid int - 原标签
    * @param $name string - 新标签名称
    * @return boolean
    **/
    public function delTag($tid)
    {
        $data = array(
            'tag' => array(
                'id' => $tid
            )
        );
        return $this->postData($data, self::DEL_TAG);
    }

    /**
    * 查询用户所在标签
    * @param $openid
    * @return mixed int|boolean
    **/
    public function getUserTag($openid)
    {
        $data = array(
            'openid' => $openid
        );
        return $this->postData($data, self::USER_TAG, 'tagid_list');
    }

    /**
    * 获取标签下粉丝列表
    * 一次拉取调用最多拉取10000个关注者的OpenID
    * @param $nextid - string
    * @param $tagid - int
    * @return array
     {
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
    public function getTagUserList($tagid, $nextid = '')
    {
        $data = array(
            'tagid' => $tagid
        );
        if($nextid) {
            $data['next_openid'] = $nextid;
        }
        return $this->getData($data, self::TAG_USER_LIST);
    }

    /**
    * 批量为用户打标签,每个用户最多三个标签。
    * @param $openids - 列表个数不能超过50个
    * @param $tid
    * @return boolean
    **/
    public function batchUserTag($openids, $tid)
    {
        $data = array(
            'openid_list' => $openids,
            'tagid' => $tid,
        );
        return $this->postData($data, self::BATCH_TAG);
    }

    /**
    * 批量取消用户标签
    * @param $openids - 列表个数不能超过50个
    * @param $tid
    * @return boolean
    **/
    public function batchUserUntag($openids, $tid)
    {
        $data = array(
            'openid_list' => $openids,
            'tagid' => $tid,
        );
        return $this->postData($data, self::BATCH_UNTAG);
    }

    /**
     * 获取标签消息
     * @return $tag
      {
        {
            "id":0,
            "name":"未标签",
            "count":标签内用户数量
         },
         {
            "id":1,
            "name":"黑名单",
            "count":"..."
         },
         {
            "id":2,
            "name":"星标组",
            "count":"...."
         }
      }
     */
    public function getTagList()
    {
        return $this->getData(array(), self::TAG_LIST, 'tags');
    }
}
?>
