<?php
namespace Wechat\Api;
/**
    * @description 获取微信统计数据
    * @author haifei.shen@eub-inc.com
    * @version 1.0
    * @createDate 2015/1/20
    * @modify 2016/03/28
	* 注意:
	* 1、接口侧的公众号数据的数据库中仅存储了2014年12月1日之后的数据，将查询不到在此之前的日期，即使有查到，也是不可信的脏数据；
	* 2、请开发者在调用接口获取数据后，将数据保存在自身数据库中，即加快下次用户的访问速度，也降低了微信侧接口调用的不必要损耗。
	* 3、额外注意，获取图文群发每日数据接口的结果中，只有中间页阅读人数+原文页阅读人数+分享转发人数+分享转发次数+收藏次数 >=3的结果才会得到统计，过小的阅读量的图文消息无法统计。
    */
class Data extends Core 
{

	// 用户分析数据接口 -s
	// 获取用户增减数据
	const GET_USER_SUMMARY = '/datacube/getusersummary';
	// 获取累计用户数据
	const GET_USER_CUMULATE = '/datacube/getusercumulate';
	// 用户分析数据接口 -e

	// 图文分析数据接口 -s
	// 获取图文群发每日数据
	const GET_ARTICLE_SUMMARY = '/datacube/getarticlesummary';
	// 获取图文群发总数据
	const GET_ARTICLE_TOTAL = '/datacube/getarticletotal';
	// 获取图文统计数据
	const GET_USER_READ = '/datacube/getuserread';
	// 获取图文统计分时数据
	const GET_USER_READ_HOUR = '/datacube/getuserreadhour';
	// 获取图文分享转发数据
	const GET_USER_SHARE = '/datacube/getusershare';
	// 获取图文分享转发分时数据
	const GET_USER_SHARE_HOUR = '/datacube/getusersharehour';
	// 图文分析数据接口 -e

	// 消息分析数据接口 -s
	// 获取消息发送概况数据
	const GET_UP_STREAM_MSG = '/datacube/getupstreammsg';
	// 获取消息分送分时数据
	const GET_UP_STREAM_MSG_HOUR = '/datacube/getupstreammsghour';
	// 获取消息发送周数据
	const GET_UP_STREAM_MSG_WEEK = '/datacube/getupstreammsgweek';
	// 获取消息发送月数据
	const GET_UP_STREAM_MSG_MONTH = '/datacube/getupstreammsgmonth';
	// 获取消息发送分布数据
	const GET_UP_STREAM_MSG_DIST = '/datacube/getupstreammsgdist';
	// 获取消息发送分布周数据
	const GET_UP_STREAM_MSG_DIST_WEEK = '/datacube/getupstreammsgdistweek';
	// 获取消息发送分布月数据
	const GET_UP_STREAM_MSG_DIST_MONTH = '/datacube/getupstreammsgdistmonth';
	// 消息分析数据接口 -e

	// 接口分析数据接口-s
	// 获取接口分析数据
	const GET_INTER_FACE_SUMMARY = '/datacube/getinterfacesummary';
	// 获取接口分析分时数据
	const GET_INTER_FACE_SUMMARY_HOUR = '/datacube/getinterfacesummaryhour';
	// 接口分析数据接口 -e

    public function __construct() 
    {
		parent::__construct ();
	}

	/**
	 * 获取用户增减数据
	 * @param array $params 最大时间跨度 7days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-07");
	 * @return
	  {
	 	"list": [
	 		{
	 			"ref_date": 数据的日期,
	  			"user_source": 用户的渠道，数值代表的含义如下：
	 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页） 57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告,
	 			"new_user":新增的用户数量,
	 			"cancel_user":取消关注的用户数量，new_user减去cancel_user即为净增用户数量
	 		},
	 		//后续还有ref_date在begin_date和end_date之间的数据
	  	]
	  }
	*/
    public function getUserSummary($params)
    {
		return $this->postData($params, self::GET_USER_SUMMARY, 'list');
	}

	/**
	 * 获取累计用户数据
	 * @param array $params 最大时间跨度 7days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-07");
	 * @return 
	   {
	  	"list": [
	 		{
	 		"ref_date":数据的日期,
	 		"cumulate_user":总用户量
	 		},
	 		//后续还有ref_date在begin_date和end_date之间的数据
	 	]
	  }
	*/
    public function getUserCumulate($params)
    {
		return $this->postData($params, self::GET_USER_CUMULATE, 'list');
	}

	/**
	 * 获取图文群发每日数据 - 文章到该日为止的总量,而不是当日的量
	 * @param array $params 最大时间跨度 1days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-01");
	 * @return 
	  {
	  	"list":[
	 	  {
	 		"ref_date": 日期，
	 		"msgid": 群发接口调用后返回的msg_data_id和index（消息次序索引）组成， 例如12003_3， 其中12003是msgid，即一次群发的消息的id； 3为index，假设该次群发的图文消息共5个文章（因为可能为多图文），3表示5个中的第3个，
	 		"title": 图文消息的标题，
	 		"int_page_read_user": 图文页（点击群发图文卡片进入的页面）的阅读人数,
	 		"int_page_read_count": 图文页的阅读次数,
	 		"ori_page_read_user": 原文页（点击图文页“阅读原文”进入的页面）的阅读人数，无原文页时此处数据为0,
	 		"ori_page_read_count": 原文页的阅读次数, 
	 		"share_user": 分享的人数,
	 		"share_count": 分享的次数,
	 		"add_to_fav_user": 收藏的人数,
	 		"add_to_fav_count": 收藏的次数,
	 	  },
	      //后续会列出该日期内所有被阅读过的文章（仅包括群发的文章）在当天的阅读次数等数据
	 	]
	  }
	*/
    public function getArticleSummary($params)
    {
		return $this->postData($params, self::GET_ARTICLE_SUMMARY, 'list');
	}
	
	/**
	 * 获取图文群发总数据 - 某天群发的文章，从群发日起到接口调用日（但最多统计发表日后7天数据），每天的到当天的总等数据
	 * @param array $params 最大时间跨度 1days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-01");
	 * @return 
	  {
	 	"list":[
	 		{
	 			"ref_date": 日期,
				"msgid": 图文消息id,
				"title": 图文消息的标题,
				"details": [
					{
						"stat_date": 统计的日期,
						"target_user": 送达人数，一般约等于总粉丝数（需排除黑名单或其他异常情况下无法收到消息的粉丝）,
						"int_page_read_user": 图文页（点击群发图文卡片进入的页面）的阅读人数,
						"int_page_read_count": 图文页的阅读次数,
						"ori_page_read_user": 原文页（点击图文页“阅读原文”进入的页面）的阅读人数，无原文页时此处数据为0,
						"ori_page_read_count": 原文页的阅读次数,
						"share_user": 分享的人数,
						"share_count": 分享的次数,
						"add_to_fav_user": 收藏的人数,
						"add_to_fav_count": 收藏的次数,
						"int_page_from_session_read_user": 公众号会话阅读人数,
						"int_page_from_session_read_count": 公众号会话阅读次数,
						"int_page_from_hist_msg_read_user": 历史消息页阅读人数,
						"int_page_from_hist_msg_read_count": 历史消息页阅读次数,
						"int_page_from_feed_read_user": 朋友圈阅读人数,
						"int_page_from_feed_read_count": 朋友圈阅读次数,
						"int_page_from_friends_read_user": 好友转发阅读人数,
						"int_page_from_friends_read_count": 好友转发阅读次数,
						"int_page_from_other_read_user": 其他场景阅读人数,
						"int_page_from_other_read_count": 其他场景阅读次数,
						"feed_share_from_session_user": 公众号会话转发朋友圈人数,
						"feed_share_from_session_cnt": 公众号会话转发朋友圈次数,
						"feed_share_from_feed_user": 朋友圈转发朋友圈人数,
						"feed_share_from_feed_cnt": 朋友圈转发朋友圈次数,
						"feed_share_from_other_user": 其他场景转发朋友圈人数,
						"feed_share_from_other_cnt": 其他场景转发朋友圈次数,
					},
					//后续还会列出所有stat_date符合“ref_date（群发的日期）到接口调用日期”（但最多只统计7天）的数据
				]
	 		},
	 		//后续还有ref_date（群发的日期）在begin_date和end_date之间的群发文章的数据
	 	]
	  }
	*/
    public function getArticleTotal($params)
    {
		return $this->postData($params, self::GET_ARTICLE_TOTAL, 'list');
	}
	
	/**
	 * 获取图文统计数据
	 * @param array $params 最大时间跨度 3days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-03");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"int_page_read_user": 图文页（点击群发图文卡片进入的页面）的阅读人数,
				"int_page_read_count": 图文页的阅读次数,
				"ori_page_read_user": 原文页（点击图文页“阅读原文”进入的页面）的阅读人数，无原文页时此处数据为0,
				"ori_page_read_count": 原文页的阅读次数,
				"share_user": 分享的人数,
				"share_count": 分享的次数,
				"add_to_fav_user": 收藏的人数,
				"add_to_fav_count": 收藏的次数,
			},
			//后续还有ref_date在begin_date和end_date之间的数据
		]
	   }
	*/
    public function getUserRead($params)
    {
		return $this->postData($params, self::GET_USER_READ, 'list');
	}
	
	/**
	 * 获取图文统计分时数据
	 * @param array $params 最大时间跨度 1days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-01");
	 * @return
	   {
	    {
	   	"list":[
			{
				"ref_date": 日期,
				"ref_hour": 小时，包括从000到2300，分别代表的是[000,100)到[2300,2400)，即每日的第1小时和最后1小时,
				"user_source": 0:会话;1.好友;2.朋友圈;3.腾讯微博;4.历史消息页;5.其他,
				"int_page_read_user": 图文页（点击群发图文卡片进入的页面）的阅读人数,
				"int_page_read_count": 图文页的阅读次数,
				"ori_page_read_user": 原文页（点击图文页“阅读原文”进入的页面）的阅读人数，无原文页时此处数据为0,
				"ori_page_read_count": 原文页的阅读次数,
				"share_user": 分享的人数,
				"share_count": 分享的次数,
				"add_to_fav_user": 收藏的人数,
				"add_to_fav_count": 收藏的次数,
			},
			//后续还有ref_hour逐渐增大,以列举1天24小时的数据
		]
        }
	   }
	*/
    public function getUserReadHour($params)
    {
		return $this->postData($params, self::GET_USER_READ_HOUR, 'list');
	}
	
	/**
	 * 获取图文分享转发数据
	 * @param array $params 最大时间跨度 7days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-07");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"share_user": 分享的人数,
				"share_count": 分享的次数,
				"share_user": 1代表好友转发 2代表朋友圈 3代表腾讯微博 255代表其他,
			},
			//后续还有不同share_scene（分享场景）的数据，以及ref_date在begin_date和end_date之间的数据
		]
	   }
	*/
    public function getUserShare($params)
    {
		return $this->postData($params, self::GET_USER_SHARE, 'list');
	}

	/**
	 * 获取图文分享转发分时数据
	 * @param array $params 最大时间跨度 7days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-07");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"ref_hour": 小时，包括从000到2300，分别代表的是[000,100)到[2300,2400)，即每日的第1小时和最后1小时,
				"share_user": 分享的人数,
				"share_count": 分享的次数,
				"share_user": 1代表好友转发 2代表朋友圈 3代表腾讯微博 255代表其他,
			},
			//后续还有不同share_scene的数据，以及ref_hour逐渐增大的数据。由于最大时间跨度为1，所以ref_date此处固定
		]
	   }
	*/
    public function getUserShareHour($params)
    {
		return $this->postData($params, self::GET_USER_SHARE_HOUR, 'list');
	}

	/**
	 * 获取消息发送概况数据
	 * @param array $params 最大时间跨度 7days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-03");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"msg_type": 1代表文字 2代表图片 3代表语音 4代表视频 6代表第三方应用消息（链接消息）,
				"msg_user": 上行发送了（向公众号发送了）消息的用户数,
				"msg_count": 上行发送了消息的消息总数
			},
			//后续还有同一ref_date的不同msg_type的数据，以及不同ref_date（在时间范围内）的数据
		]
	   }
	*/
    public function getUpStreamMsg($params)
    {
		return $this->postData($params, self::GET_UP_STREAM_MSG, 'list');
	}

	/**
	 *  获取消息分送分时数据
	 * @param array $params 最大时间跨度 1days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-03");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"ref_hour": 小时，包括从000到2300，分别代表的是[000,100)到[2300,2400)，即每日的第1小时和最后1小时,
				"msg_type": 1代表文字 2代表图片 3代表语音 4代表视频 6代表第三方应用消息（链接消息）,
				"msg_user": 上行发送了（向公众号发送了）消息的用户数,
				"msg_count": 上行发送了消息的消息总数
			},
			//后续还有同一ref_hour的不同msg_type的数据，以及不同ref_hour的数据，ref_date固定，因为最大时间跨度为1
		]
	   }
	*/
    public function getUpStreamMsgHour($params)
    {
		return $this->postData($params, self::GET_UP_STREAM_MSG_HOUR, 'list');
	}

	/**
	 * 获取消息发送周数据
	 * @param array $params 最大时间跨度 30days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-03");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"msg_type": 1代表文字 2代表图片 3代表语音 4代表视频 6代表第三方应用消息（链接消息）,
				"msg_user": 上行发送了（向公众号发送了）消息的用户数,
				"msg_count": 上行发送了消息的消息总数
			},
			//后续还有同一ref_date下不同msg_type的数据，及不同ref_date的数据
		]
	   }
	*/
    public function getUpStreamMsgWeek($params)
    {
		return $this->postData($params, self::GET_UP_STREAM_MSG_WEEK, 'list');
	}

	/**
	 * 获取消息发送月数据
	 * @param array $params 最大时间跨度 30days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-03");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"msg_type": 1代表文字 2代表图片 3代表语音 4代表视频 6代表第三方应用消息（链接消息）,
				"msg_user": 上行发送了（向公众号发送了）消息的用户数,
				"msg_count": 上行发送了消息的消息总数
			},
			//后续还有同一ref_date下不同msg_type的数据，及不同ref_date的数据
		]
	   }
	*/
    public function getUpStreamMsgMonth($params)
    {
		return $this->postData($params, self::GET_UP_STREAM_MSG_MONTH, 'list');
	}

	/**
	 * 获取消息发送分布数据
	 * @param array $params 最大时间跨度 15days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-03");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"count_interval": 当日发送消息量分布的区间，0代表 “0”，1代表“1-5”，2代表“6-10”，3代表“10次以上”,
				"msg_user": 上行发送了（向公众号发送了）消息的用户数,
			},
			//后续还有同一ref_date下不同count_interval的数据，及不同ref_date的数据
		]
	   }
	*/
    public function getUpStreamMsgDist($params)
    {
		return $this->postData($params, self::GET_UP_STREAM_MSG_DIST, 'list');
	}

	/**
	 * 获取消息发送分布周数据
	 * @param array $params 最大时间跨度 30days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-03");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"count_interval": 当日发送消息量分布的区间，0代表 “0”，1代表“1-5”，2代表“6-10”，3代表“10次以上”,
				"msg_user": 上行发送了（向公众号发送了）消息的用户数,
			},
			//后续还有同一ref_date下不同count_interval的数据，及不同ref_date的数据
		]
	   }
	*/
    public function getUpStreamMsgDistWeek($params)
    {
		return $this->postData($params, self::GET_UP_STREAM_MSG_DIST_WEEK, 'list');
	}

	/**
	 * 获取消息发送分布月数据
	 * @param array $params 最大时间跨度 30days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-03");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"count_interval": 当日发送消息量分布的区间，0代表 “0”，1代表“1-5”，2代表“6-10”，3代表“10次以上”,
				"msg_user": 上行发送了（向公众号发送了）消息的用户数,
			},
			//后续还有同一ref_date下不同count_interval的数据，及不同ref_date的数据
		]
	   }
	*/
    public function getUpStreamMsgDistMonth($params)
    {
		return $this->postData($params, self::GET_UP_STREAM_MSG_DIST_MONTH, 'list');
	}

	/**
	 * 获取接口分析数据
	 * @param array $params 最大时间跨度 30days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-03");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"callback_count": 通过服务器配置地址获得消息后，被动回复用户消息的次数,
				"fail_count": 上述动作的失败次数,
				"total_time_cost": 总耗时，除以callback_count即为平均耗时,
				"max_time_cost": 最大耗时
			},
			//后续还有不同ref_date（在begin_date和end_date之间）的数据
		]
	   }
	*/
    public function getInterFaceSummary($params)
    {
		return $this->postData($params, self::GET_INTER_FACE_SUMMARY, 'list');
	}

	/**
	 *获取接口分析分时数据
	 * @param array $params 最大时间跨度 1days
	 * @example  $params = array("begin_date"=>"2015-01-01", "end_date"=>"2015-01-01");
	 * @return
	   {
	   	"list":[
			{
				"ref_date": 日期,
				"ref_hour": 小时,
				"callback_count": 通过服务器配置地址获得消息后，被动回复用户消息的次数,
				"fail_count": 上述动作的失败次数,
				"total_time_cost": 总耗时，除以callback_count即为平均耗时,
				"max_time_cost": 最大耗时
			},
			//后续还有不同ref_hour的数据
		]
	   }
	*/
    public function getInterFaceSummaryHour($params)
    {
		return $this->postData($params, self::GET_INTER_FACE_SUMMARY_HOUR, 'list');
	}
}
