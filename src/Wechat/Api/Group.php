<?php
namespace Wechat\Api;
    /**
* @description 用户分组
* @author shf1986@gmail.com
* @version 1.0
* @createDate 2015/1/7
* @modify
* 一个公众账号，最多支持创建100个分组
*/
class Group extends Core
{
    //创建用户分组
    const CREATE_GROUP_URL = '/cgi-bin/groups/create';

    //删除用户分组
    const DEL_GROUP_URL = '/cgi-bin/groups/delete';

    //获取所有分组
    const GROUP_LIST_URL   = '/cgi-bin/groups/get';

    //查询用户所在分组
    const USER_GROUP_URL   = '/cgi-bin/groups/getid';

    //修改分组名称
    const MODIFY_GROUP_URL = '/cgi-bin/groups/update';

    //移动用户分组
    const MOVE_GROUP_URL   = '/cgi-bin/groups/members/update';

    //批量移动用户分组
    const BATCH_MOVE_GROUP_URL = '/cgi-bin/groups/members/batchupdate';

    public function __construct()
    {
        parent::__construct();
    }

    /**
    * 创建分组
    * @param $name - string - 30个字符以内,UTF8编码
    * @return $groupid - boolean|int
    **/
    public function createUserGroup($name)
    {
        $data = array(
            'group'=>array(
                'name'=>urlencode($name)
            )
        );
        return $this->postData($data, self::CREATE_GROUP_URL, 'group');
    }

    /**
    * 修改分组名称
    * @param $gid int - 原分组
    * @param $name string - 新分组名称
    * @return boolean
    **/
    public function modifyUserGroup($gid,$name)
    {
        $data = array(
            'group' => array(
                'id' => $gid,
                'name' => urlencode($name)
            )
        );
        return $this->postData($data, self::MODIFY_GROUP_URL);
    }

    /**
    * 删除分组 - 分组删除后，所有该分组内的用户自动进入默认分组，只能删除小于10000的用户分组
    * @param $gid int - 原分组
    * @param $name string - 新分组名称
    * @return boolean
    **/
    public function delUserGroup($gid)
    {
        $data = array(
            'group' => array(
                'id' => $gid
            )
        );
        return $this->postData($data, self::DEL_GROUP_URL);
    }

    /**
    * 查询用户所在分组
    * @param $openid
    * @return mixed int|boolean
    **/
    public function getUserGroup($openid)
    {
        $data = array(
            'openid' => $openid
        );
        return $this->postData($data, self::USER_GROUP_URL, 'groupid');
    }

    /**
    * 移动用户分组
    * @param $openid
    * @param $gid
    * @return boolean
    **/
    public function moveUserGroup($openid,$gid)
    {
        $data = array(
            'openid' => $openid,
            'to_groupid' => $gid,
        );
        return $this->postData($data, self::MOVE_GROUP_URL);
    }

    /**
    * 移动用户分组
    * @param $openids - size不超过50
    * @param $gid
    * @return boolean
    **/
    public function moveBatchUserGroup($openids, $gid)
    {
        $data = array(
            'openid_list' => $openids,
            'to_groupid' => $gid,
        );
        return $this->postData($data, self::BATCH_MOVE_GROUP_URL);
    }

    /**
     * 获取分组消息
     * @return $group
      {
        {
            "id":0,
            "name":"未分组",
            "count":分组内用户数量
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
    public function getGroupList()
    {
        $data = array();
        return $this->getData($data, self::GROUP_LIST_URL, 'groups');
    }
}
?>
