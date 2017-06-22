<?php
namespace Wechat\Api;
/**
* @description 创建微信菜单
* @author shf1986@gmail.com
* @version 1.0
* @createDate 2015/1/7
* @modify
*/
class Menu extends Core
{
    //查询菜单
    const GET_MENU_URL          = '/cgi-bin/menu/get';

    public function __construct()
    {
        parent::__construct();
    }

    /**
    * 查询自定义菜单
    * @return
     {
        "menu":{
            "button":[
            {
                "type": "click",
                "name": "***",
                "key" : "***
            },
            {
                "type": "view",
                "name": "***",
                "url" : "***
            },
            {
                "name": "***",
                "sub_button" : [
    
                ]
            },
            ],
            "menuid":208396938 //有个性化菜单时
        },
        "conditionalmenu":[ //有个性化菜单时
            {
                "button":[
                    //同上
                ],
                "matchrule": {
                    "tag_id": 标签ID, 
                    "sex"     : 性别,
                    "country" : 国家,
                    "province": 省份,
                    "city"    : 城市,
                    "client_platform_type" : 客户端版本，当前只具体到系统型号：IOS(1), Android(2),Others(3)，不填则不做匹配 
                    "language": 语言信息，是用户在微信中设置的语言，具体请参考语言表：1、简体中文 "zh_CN" 2、繁体中文TW "zh_TW" 3、繁体中文HK "zh_HK" 4、英文 "en" 5、印尼 "id" 6、马来 "ms" 7、西班牙 "es" 8、韩国 "ko" 9、意大利 "it" 10、日本 "ja" 11、波兰 "pl" 12、葡萄牙 "pt" 13、俄国 "ru" 14、泰文 "th" 15、越南 "vi" 16、阿拉伯语 "ar" 17、北印度 "hi" 18、希伯来 "he" 19、土耳其 "tr" 20、德语 "de" 21、法语 "fr"
                },
                "menuid":菜单ID
            }
        
        ]
     }
    **/
    public function getMenu()
    {
        return $this->getData(array(), self::GET_MENU_URL);
    }
}
?>
