<?php 
/**
 * @author huanghailong <hailong.huang@eub-inc.com>
 * @version 2017-03-15
 * @internal mp 平台推送数据整理基础类
 * @command --file=文件路径
 */
namespace Process\Mp;

use Wechat;
use Model\Mp\Push;
use Util\Log as Log;

class Queue {
    /**
     * 批量插入最大数据数 
     *
     * @var int
     */
    public $max_batch_limit = 500;
	/**
	 * drive
	 *
	 * @var mixed
	 */
	protected $drive;

    /**
     * redis
     *
     * @var mixed
     */
    protected $redis;

    /**
     * 数据处理类 
     *
     * @var mixed
     */
    protected $decrypt;

    /**
     * 消息队列名称 
     *
     * @var mixed
     */
    protected $queue_key;

	/**
	 * 插入记录数 
	 *
	 * @var mixed
	 */
	protected $insert_len;

	/**
	 * 失败记录数 
	 *
	 * @var mixed
	 */
	protected $error_len;
    
    /**
     * 本次操作数据数 
     *
     * @var mixed
     */
    protected $data_len;

    
	/**
	 * 更新记录数 
	 *
	 * @var mixed
	 */
	protected $update_len;

    /**
     * 存在数据数 
     *
     * @var mixed
     */
    protected $exists_len;

    /**
     * 
     * 插入失败事件
     * @var mixed
     */
    protected $failed_event_data;
    
    /**
     * 插入失败消息 
     *
     * @var mixed
     */
    protected $failed_message_data;

    /**
     * __construct
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-21
     *
     * @param mixed $param
     * @return void
     */
    function __construct ($config)
    {

        //数据库操作类
		$this->drive['event'] = new \Model\Mp\Push\Event($config['db']);
		$this->drive['message'] = new \Model\Mp\Push\Message($config['db']);
        
        //redis实例
        try{
            $this->redis = new \Redis();
            $this->redis->connect($config['redis']['host'], $config['redis']['port']);
            $this->redis->auth($config['redis']['auth']);
            $this->redis->select($config['redis']['index']);
        } catch (RedisException $e) {
            exit('Redis has going away....');
        }

        //消息校验及解密类
        //$this->secret = new \Wechat\Secret($config['wx']['token'], $config['wx']['app_id'], $config['wx']['aes_key']);

        //消息队列 
        $this->queue_key = $config['redis']['message_queue'];
	}

	/**
	 * 将 队列数据 数据按类型拆分入库
	 * @return [type] [description]
	 */
    public function handle ()
    {

        while ($message = $this->redis->rPop($this->queue_key)) {
            $this->data_len++;
            $data = json_decode($message, true);
            //$message = json_decode($message, true);
            //if ($this->secret->validate($message['signature'], $message['timestamp'], $message['nonce'])) {
            //    $data = (array) simplexml_load_string($message['data'], 'SimpleXMLElement', LIBXML_NOCDATA); 
            //    $data = array_change_key_case($data, CASE_LOWER);
            //    $result = $this->secret->decrypt($data);
            //    if ($result['error']) {
                    //log
            //        Log::error('QueueRawdata', __CLASS__, 'decrypt', $message);
            //        continue; 
            //    }
            //    $data = $result['data'];

                if ('event' == $data['msgtype']) {

                    // 当 MsgType 为 event 时：
                    // 将 ToUserName、FromUserName、Event、CreateTime 连接；
                    // 做 md5 操作；
                    // 生成 EventId；
                    // 以便重复插入时 可以触发 `ON DUPLICATE KEY UPDATE` 操作
                    $data['eventid'] = md5($data['tousername'].$data['fromusername'].$data['event'].$data['createtime']);

                    // 设置处理消息实例
                    $Drive = $this->drive['event'];
                    
                    $this->failed_event_data[] = $data;
                } else {

                    // 设置处理消息实例
                    $Drive = $this->drive['message'];

                    $this->failed_message_data[] = $data;
                }

                $Drive->format($data);

                //当前事件类型数据记录数
                $eventCnt = $this->drive['event']->getRecordCount();
                //当前消息类型数据记录数
                $messageCnt = $this->drive['message']->getRecordCount();

                //执行批量插入
                if (($eventCnt + $messageCnt) > $this->max_batch_limit) {
                    //事件类型数据批量插
                    $this->drive['event']->batchInsert();
                    if ($this->drive['event']->error()[1]) {
                        Log::error('QueueDecryptData', strtolower(str_replace('\\', '-', __CLASS__)), 'database', $this->failed_event_data);
                        $this->error_len += $eventCnt;
                    } else {
                        $this->insert_len += $eventCnt;
                        $this->drive['event']->reset();
                        $this->failed_event_data = []; 
                    }

                    //消息类型数据批量插入
                    $this->drive['message']->batchInsert();
                    if ($this->drive['message']->error()[1]) {
                        $this->error_len += $messageCnt;
                        Log::error('QueueDecryptData', strtolower(str_replace('\\', '-', __CLASS__)), 'database', $this->failed_message_data);
                    } else {
                        $this->insert_len += $messageCnt;
                        $this->drive['message']->reset();
                        $this->failed_message_data = [];
                    }
                }
            //} else {
                //log
            //    Log::error('QueueRawData', __CLASS__, 'invalid', $message);
            //}
        }

        if ($this->redis->llen($this->queue_key)) {
            return true;
        } else {
            //事件类型数据批量插
            $this->drive['event']->batchInsert();
            if ($this->drive['event']->error()[1]) {
                Log::error('QueueDecryptData', strtolower(str_replace('\\', '-', __CLASS__)), 'database', $this->failed_event_data);
                $this->error_len += $eventCnt;
            } else {
                $this->insert_len += $eventCnt;
                $this->drive['event']->reset();
                $this->failed_event_data = []; 
            }

            //消息类型数据批量插入
            $this->drive['message']->batchInsert();
            if ($this->drive['message']->error()[1]) {
                $this->error_len += $messageCnt;
                Log::error('QueueDecryptData', strtolower(str_replace('\\', '-', __CLASS__)), 'database', $this->failed_message_data);
            } else {
                $this->insert_len += $messageCnt;
                $this->drive['message']->reset();
                $this->failed_message_data = [];
            }
            return false;
        }
	}

	/**
	 * run
	 * @author Jerry Shen <haifei.shen@eub-inc.com>
	 * @version 2017-06-21
	 *
	 * @return void
	 */
	public function run () {

		$start_time = date('Y-m-d H:i:s', time());

		while (1) {

			$result = $this->handle();

			if ($result === false) {

				break;

			}

		}

		$finish_time = date('Y-m-d H:i:s', time());

		return [
			'start_time'  =>$start_time,
			'insert_len'  =>$this->insert_len,
			'error_len'   =>$this->error_len,
			'data_len'    =>$this->data_len,
			'update_len'  =>$this->update_len,
			'exists_len'  =>$this->exists_len,
			'finish_time' =>$finish_time,
		];

	}

}
