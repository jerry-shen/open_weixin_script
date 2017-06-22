# ************************************************************
# Sequel Pro SQL dump
# Version 4468
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 47.92.134.241 (MySQL 5.6.36)
# Database: open_wx_db
# Generation Time: 2017-06-22 06:33:21 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table ow_mp_push_event
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ow_mp_push_event`;

CREATE TABLE `ow_mp_push_event` (
  `event_id` varchar(64) NOT NULL COMMENT '事件 ID，通过MD5计算 app_id+open_id+event_type+create_time 的混合值得出',
  `app_id` varchar(64) NOT NULL COMMENT '微信公众号原始ID，对应MP平台推送消息事件中的 ToUserName',
  `open_id` varchar(64) NOT NULL COMMENT '用户的 OpenID，对应MP平台推送消息事件中的 FromUserName',
  `event_type` enum('click','kf_close_session','kf_create_session','kf_switch_session','location','masssendjobfinish','scan','shakearoundusershake','subscribe','unsubscribe','templatesendjobfinish','view','other') NOT NULL COMMENT '对应MP平台推送消息事件中的 Event',
  `event_key` varchar(255) NOT NULL DEFAULT '' COMMENT '事件值',
  `extra` text NOT NULL COMMENT '扩展信息',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录创建的日期，对应推送消息中的 CreateTime',
  PRIMARY KEY (`event_id`),
  KEY `app_id` (`app_id`,`event_type`,`event_key`(191),`create_time`),
  KEY `event_id_2` (`event_id`),
  KEY `event_id_3` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信接收事件推送-基本信息表';



# Dump of table ow_mp_push_message
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ow_mp_push_message`;

CREATE TABLE `ow_mp_push_message` (
  `msg_id` varchar(64) NOT NULL COMMENT '消息 ID',
  `app_id` varchar(64) NOT NULL COMMENT '微信公众号原始ID',
  `open_id` varchar(64) NOT NULL COMMENT '发送信息的用户 openID',
  `msg_type` enum('text','image','voice','video','shortvideo','location','link') NOT NULL COMMENT '消息类型',
  `msg_content` varchar(1000) NOT NULL DEFAULT '' COMMENT '消息内容',
  `extra` text NOT NULL COMMENT '扩展信息',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '消息创建时间',
  PRIMARY KEY (`msg_id`),
  KEY `app_id` (`app_id`,`msg_type`,`msg_content`(191),`create_time`),
  KEY `msg_id_2` (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信接收普通消息-基础信息表';




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
