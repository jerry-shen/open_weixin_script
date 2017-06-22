<?php 
namespace Model\Mp\Push;

use Util\Model;

/**
 * 微信事件类型数据 
 *
 * @see Model
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-06-21
 */
class Event extends Model
{

	// base table 名称 及 脚本log文件名称
	protected $tableName = 'ow_mp_push_event';

	// api 字段与数据库字段对应表
	protected $tableFields = [ 
        'eventid'       => 'event_id',
        'tousername'    => 'app_id',
        'fromusername'  => 'open_id',
        'event'         => 'event_type',
        'eventkey'      => 'event_key',
        'createtime'    => 'create_time',
        'extra'         => 'extra',
    ];
}
