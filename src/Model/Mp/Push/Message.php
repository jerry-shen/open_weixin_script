<?php 
namespace Model\Mp\Push;

use Util\Model;

/**
 * 微信消息类型数据 
 *
 * @see Model
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-06-21
 */
class Message extends Model
{

	// base table 名称 及 脚本log文件名称
	protected $tableName = 'ow_mp_push_message';

	// api 字段与数据库字段对应表
	protected $tableFields = [
        'msgid'        => 'msg_id',
        'tousername'   => 'app_id',
        'fromusername' => 'open_id',
        'msgtype'      => 'msg_type',
        'content'      => 'msg_content',
        'mediaId'      => 'msg_content',
        'label'        => 'msg_content',
        'createtime'   => 'create_time',
        'extra'        => 'extra',
    ];
}
