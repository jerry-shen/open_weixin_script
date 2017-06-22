<?php
namespace Wechat\Data\Custom;

use Wechat\Api;

/**
 * Class: Record
 * 获取聊天记录
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-03-07
 */
class Record 
{
    //错误
    public $error;

    //调试模式
    protected $debug;

    //接口token
    protected $token;

    /**
     * 每次获取条数，最多10000条 
     */
    const MAX_LIMIT = 10000;

    /**
     * 最大时间跨度 
     */
    const MAX_STEP  = 86400;

    /**
     * __construct
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-03-03
     *
     * @return void
     */
    public function __construct($token, $debug = false)
    {
        $this->token = $token; 
        $this->debug = $debug;
    }

    /**
     * __destruct
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-03-03
     *
     * @return void
     */
    public function __destruct()
    {
    
    }

    /**
     * fetch
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-03-07
     *
     * @param mixed $param
     * @return void
     */
    public function fetch($param)
    {
        $wxApi = new \Wechat\Api\Custom;
        $wxApi->setAccessToken($this->token);
        $result = call_user_func_array(array($wxApi, 'getCustomRecord'), $param); 
        if (empty($result)) {
            $this->error = var_export($wxApi->getError(), true);
            return false;
        }
        return $result;
    }

    /**
     * run
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-03-07
     *
     * @param mixed $start
     * @param mixed $end
     * @param int $msgid
     * @param int $number
     * @return void
     */
    public function run($start, $end, $msgid = 1, $number = 10000)
    {
        $dayDiff = (strtotime($start) - strtotime($end));
        
        if ($dayDiff > self::MAX_STEP) {
            $this->error = "查询时段不能超过24小时";
            return false;
        }

        if ($number > self::MAX_LIMIT) {
            $this->error = "每次获取最多10000条";
            return false;
        }

        $param = array(
            'starttime' => strtotime($start),
            'endtime'   => strtotime($end),
            'msgid'     => $msgid,
            'number'    => $number 
        );
        return $this->fetch($param);
    }
}
?>

