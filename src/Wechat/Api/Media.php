<?php
namespace Wechat\Api;
/**
* @description 多媒体素材
* @author shf1986@gmail.com
* @version 1.0
* @createDate 2015/1/7
* @modify
* 注意：
* 1、对于临时素材，每个素材（media_id）会在开发者上传或粉丝发送到微信服务器3天后自动删除（所以用户发送给开发者的素材，若开发者需要，应尽快下载到本地），以节省服务器资源。
* 2、media_id是可复用的。
* 3、素材的格式大小等要求与公众平台官网一致。具体是，图片大小不超过2M，支持bmp/png/jpeg/jpg/gif格式，语音大小不超过5M，长度不超过60秒，支持mp3/wma/wav/amr格式
* 4、需使用https调用本接口。
*/
class Media extends Core
{
    //下载临时的多媒体文件
    const GET_MEDIA_URL    = '/cgi-bin/media/get';

    //下载临时的多媒体文件
    const GET_HDVOICE_URL    = '/cgi-bin/media/jssdk';

    //下载临时的多媒体文件-视频
    const GET_VIDEO_URL    = 'http://api.weixin.qq.com/cgi-bin/media/get';

    //获取永久素材
    const GET_PERMANENT_MEDIA_URL = '/cgi-bin/material/get_material';

    //获取素材综述
    const GET_PERMANENT_MEDIA_COUNT_URL = '/cgi-bin/material/get_materialcount';

    //获取素材列表
    const GET_PERMANENT_MEDIA_LIST_URL = '/cgi-bin/material/batchget_material';

    public function __construct()
    {
        parent::__construct();
    }

    /*
    * 下载多媒体文件 - 视频文件不支持下载
    * @param string $mediaId - 资源ID
    * @param string $filename - 保存文件名
    * @param boolean $isForward 是否直接转发
    * @return string boolean|$file - 文件路径
    */
    public function downloadMedia($mediaId, $filename = '', $type = '', $isForward = false)
    {
        $request = $this->request();
        if ('video' == $type) {
            $request->url = self::WEIXIN_HOST .self::GET_VIDEO_URL;
        } elseif ('hdvoice' == $type) {
            $request->url = self::WEIXIN_HOST .self::GET_HDVOICE_URL;
        } else {
            $request->url = self::WEIXIN_HOST .self::GET_MEDIA_URL;
        }
        $request->parameters = array(
            'access_token' => $this->token,
            'media_id' => $mediaId
        );
        $response = $request->get();
        if ($isForward) {
            return $response;
        }
        if ($file = $response->save($filename)) {
            return $file;
        } else {
            $return = json_decode($response->body,true);
            $this->setError($return);
            return false;
        }
    }

    /**
    * 获取永久素材
    * @param $data - array
    * @param $type - string
    * @return boolean|array
    * simple array(
    *	'media_id':MEDIA_ID
    * )
    **/
    public function getPermanentMaterial($data, $type = 'news')
    {
        $request = $this->request();
        $request->url = self::WEIXIN_HOST . self::GET_PERMANENT_MEDIA_URL;
        $request->parameters = array(
            'access_token' => $this->token
        );
        $request->body = json_encode($data);

        $response = $request->post();
        $return = json_decode($response->body, true);
        if (!isset($return['errcode'])) {
            if ($type != 'news') {
                switch($type) {
                    case 'image':
                        $ext = 'png';
                        break;
                    case 'voice':
                        $ext = 'mp3';
                        break;
                    case 'video':
                        $ext = 'mp4';
                        break;
                    default:
                        $ext = 'txt';
                        break;
                }
                return $response->save();
            }
            return $return;
        } else {
            $this->setError($return);
            return false;
        }
    }

    /**
    * 获取素材总数
    * 注意：
    * 	1.永久素材的总数，也会计算公众平台官网素材管理中的素材
    *	2.图片和图文消息素材（包括单图文和多图文）的总数上限为5000，其他素材的总数上限为1000
    *	3.调用该接口需https协议
    * @return boolean|array
    * {
    * 	"voice_count":语音总数量
    *	"video_count":视频总数量
    *	"image_count":图片总数量
    *	"news_count" :图文总数量
    * }
    **/
    public function getPermanentMaterialCount()
    {
        $data = array();
        return $this->getData($data, self::GET_PERMANENT_MEDIA_COUNT_URL);
    }

    /**
    * 获取素材列表
    * @param $data - array
    * simple $data
    * array(
    *	'type' => '素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）',
    *	'offset' => '从全部素材的该偏移位置开始返回，0表示从第一个素材 返回',
    *	'count' => '返回素材的数量，取值在1到20之间'
    * )
    * @return boolean|array
    * {
    *	"total_count":素材的总数
    *	"item_count": 本次调用获取的素材的数量
    *	"item": [{
    *		"media_id":
            "name"://非图文消息 - 文件名称
            "url"://非图文消息
            "content": [{
                "title": 图文消息的标题
                "thumb_media_id": 图文消息的封面图片素材id（必须是永久mediaID）
                "show_cover_pic": 是否显示封面，0为false，即不显示，1为true，即显示
                "author":作者
                "digest": 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
                "content": 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
                "url": 图文页的URL，或者，当获取的列表是图片素材列表时，该字段是图片的URL
                "content_source_url": 图文消息的原文地址，即点击“阅读原文”后的URL
            },
            //多图文消息会在此处有多篇文章
            ]
            },
            "update_time": 这篇图文消息素材的最后更新时间
          },
          //可能有多个图文消息item结构
        ]
    * }
    **/
    public function getPermanentMaterialList($data)
    {
        return $this->postData($data, self::GET_PERMANENT_MEDIA_LIST_URL);
    }
}
?>
