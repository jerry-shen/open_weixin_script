<?php
namespace Wechat\Data\Custom;

use Wechat\Api;

/**
 * Class: Online
 * 获取在线客服列表
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-03-10
 */
class Online 
{
    //错误
    public $error;

    //调试模式
    protected $debug;

    //接口token
    protected $token;

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
     * @version 2017-03-10
     *
     * @return void
     */
    public function fetch()
    {
        $wxApi = new \Wechat\Api\Custom;
        $wxApi->setAccessToken($this->token);
        $result = $wxApi->onlineKfList(); 
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
     * @return void
     */
    public function run()
    {
        return $this->fetch();
    }
}
?>

