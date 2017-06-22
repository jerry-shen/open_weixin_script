<?php
namespace Wechat\Api;
/**
* @description 获取微信后台自动回复规则 
* @author shf1986@gmail.com
* @version 1.0
* @createDate 2015/11/17
* @modify
* 注意：
* 1、第三方平台开发者可以通过本接口，在旗下公众号将业务授权给你后，立即通过本接口检测公众号的自动回复配置，并通过接口再次给公众号设置好自动回复规则，以提升公众号运营者的业务体验。
* 2、本接口仅能获取公众号在公众平台官网的自动回复功能中设置的自动回复规则，若公众号自行开发实现自动回复，或通过第三方平台开发者来实现，则无法获取。
* 3、认证/未认证的服务号/订阅号，以及接口测试号，均拥有该接口权限。
* 4、从第三方平台的公众号登录授权机制上来说，该接口从属于消息与菜单权限集。
* 5、本接口中返回的图片/语音/视频未临时素材（临时素材每次获取都不同，3天内有效，通过素材管理-获取临时素材接口来获取这些素材），本接口返回的图文消息为永久素材素材（通过素材管理-获取永久素材接口来获取这些素材）。
*/
class Rule extends Core
{
    //长链接转成短链接
    const GET_AUTOREPLY_INFO_URL = '/cgi-bin/get_current_autoreply_info';

    public function __construct()
    {
        parent::__construct();
    }

    /**
    * 获取公众号的自动回复规则
    * @return array 
     {
        "is_add_friend_reply_open": 关注后自动回复是否开启，0代表未开启，1代表开启, 
        "is_autoreply_open": 消息自动回复是否开启，0代表未开启，1代表开启, 
        "add_friend_autoreply_info": {//关注后自动回复的信息
            "type": "text", 
            "content": "Thanks for your attention!"
        },
        "message_default_autoreply_info": { //消息自动回复的信息
            "type": "text", 
            "content": "Hello, this is autoreply!"
        },
        "keyword_autoreply_info": { //关键词自动回复的信息
            "list": [ 
                {
                    "rule_name": "autoreply-news", //规则名称
                    "create_time": 1423028166, //创建时间
                    "reply_mode": "reply_all", //回复模式，reply_all代表全部回复，random_one代表随机回复其中一条
                    "keyword_list_info": [ //匹配的关键词列表
                        {
                            "type": "text", 
                            "match_mode": "contain", //匹配模式，contain代表消息中含有该关键词即可，equal表示消息内容必须和关键词严格相同
                            "content": "news测试"//此处content即为关键词内容
                        }
                    ],
                    "reply_list_info": [ 
                        {
                            "type": "news", 
                            "news_info": { 
                                "list": [ 
                                    {
                                        "title":"",
                                        "author": "",
                                        "digest": "",
                                        "show_cover": ""
                                        "content_url": ""
                                        "source_url": ""	
                                    },
                                    {
                                        .....
                                    }
                                ]
                            },
                            .....
                        },
                        {
                        .....
                        }
                    ]
                },
                {
                    rule.....
                }
            ]
        }
     }
    **/
    public function info()
    {
        return $this->postData(array(), self::GET_AUTOREPLY_INFO_URL);
    }
}
?>
