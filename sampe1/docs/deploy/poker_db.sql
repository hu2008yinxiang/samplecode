/*
Navicat MySQL Data Transfer

Source Server         : 192.168.110.4
Source Server Version : 50540
Source Host           : 192.168.110.4:3306
Source Database       : poker_db

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2015-01-05 11:45:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for accounts
-- ----------------------------
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `account_id` int(22) unsigned NOT NULL COMMENT '账户ID',
  `account_token` varchar(512) NOT NULL COMMENT '本地账户密码',
  `bind_id` varchar(255) DEFAULT '' COMMENT '用户绑定的其他ID',
  `bind_email` varchar(255) DEFAULT NULL COMMENT '其他ID的',
  `bind_token` varchar(512) DEFAULT '' COMMENT '绑定其他账户的token',
  `bind_detail` text COMMENT '对于绑定facebook的用户，存放从facebook获得的用户信息',
  `nickname` varchar(64) NOT NULL DEFAULT '',
  `gender` enum('secret','male','female') DEFAULT 'secret',
  `photo` tinyint(3) NOT NULL DEFAULT '0' COMMENT '头像地址，-1表示需要情求/photo/account_id获取，0及以上表示内置头像',
  `type` enum('local','facebook') NOT NULL DEFAULT 'local' COMMENT '账户类型',
  `chip` bigint(22) NOT NULL DEFAULT '0' COMMENT '玩家数量筹码',
  `diamond` bigint(22) NOT NULL DEFAULT '0' COMMENT '玩家钻石数量',
  `exp` bigint(22) unsigned NOT NULL DEFAULT '0',
  `level` int(8) NOT NULL DEFAULT '1' COMMENT '玩家等级',
  `vip_score` int(11) NOT NULL DEFAULT '0' COMMENT 'VIP积分',
  `login_last` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次登录时间戳',
  `login_combo` int(8) NOT NULL DEFAULT '0' COMMENT '连续登录次数',
  `last_pay` int(11) NOT NULL DEFAULT '0' COMMENT '上次充值时间',
  `best_hand` varchar(512) NOT NULL DEFAULT '' COMMENT '最好手牌',
  `biggest_bet` bigint(22) NOT NULL DEFAULT '0' COMMENT '总共赢得筹码数',
  `biggest_win` bigint(22) unsigned NOT NULL DEFAULT '0' COMMENT '最大赢注',
  `win_round` bigint(22) unsigned NOT NULL DEFAULT '0',
  `round` bigint(22) unsigned NOT NULL DEFAULT '0',
  `last_lottery` varchar(10) NOT NULL DEFAULT '0000-00-00' COMMENT '最后一次抽奖的日期',
  `sot_stage` tinyint(1) NOT NULL DEFAULT '1',
  `ref_id` int(22) unsigned DEFAULT NULL,
  PRIMARY KEY (`account_id`),
  KEY `fk_ref_id` (`ref_id`),
  CONSTRAINT `fk_ref_id` FOREIGN KEY (`ref_id`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for achievements
-- ----------------------------
DROP TABLE IF EXISTS `achievements`;
CREATE TABLE `achievements` (
  `account_id` int(22) unsigned NOT NULL,
  `achievement_id` int(22) unsigned NOT NULL,
  `status` char(3) NOT NULL DEFAULT '0',
  `current` bigint(22) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`achievement_id`),
  CONSTRAINT `fk_achievements_account_id_accounts` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for daily_tasks
-- ----------------------------
DROP TABLE IF EXISTS `daily_tasks`;
CREATE TABLE `daily_tasks` (
  `account_id` int(22) unsigned NOT NULL COMMENT '账户id',
  `task_id` int(22) unsigned NOT NULL COMMENT '任务id',
  `current` int(4) unsigned NOT NULL COMMENT '完成进度',
  `last_day` char(8) NOT NULL COMMENT '最后更新时间',
  `rewarded` enum('true','false') NOT NULL DEFAULT 'false' COMMENT '是否领取奖励',
  PRIMARY KEY (`account_id`,`task_id`),
  CONSTRAINT `fk_daily_task_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for extras
-- ----------------------------
DROP TABLE IF EXISTS `extras`;
CREATE TABLE `extras` (
  `account_id` int(22) unsigned NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(1024) NOT NULL,
  PRIMARY KEY (`account_id`,`name`),
  CONSTRAINT `fk_extras_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for friends
-- ----------------------------
DROP TABLE IF EXISTS `friends`;
CREATE TABLE `friends` (
  `src_id` int(22) unsigned NOT NULL COMMENT '自己账户id',
  `dst_id` int(22) unsigned NOT NULL COMMENT '对方账户id',
  `status` enum('requested','added','deleted','rejected','none') NOT NULL DEFAULT 'none' COMMENT '关系状态',
  `src` enum('facebook','local') NOT NULL DEFAULT 'local' COMMENT '来源',
  `when` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间戳',
  `last_gift_day` date DEFAULT '1000-01-01' COMMENT '最后一次赠送礼物的日期',
  PRIMARY KEY (`src_id`,`dst_id`),
  KEY `fk_dst` (`dst_id`) USING BTREE,
  CONSTRAINT `fk_friends_dst_id_account_id` FOREIGN KEY (`dst_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_friends_src_id_account_id` FOREIGN KEY (`src_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for google_play_orders
-- ----------------------------
DROP TABLE IF EXISTS `google_play_orders`;
CREATE TABLE `google_play_orders` (
  `order_id` varchar(64) NOT NULL,
  `account_id` int(22) NOT NULL,
  `product_id` varchar(64) NOT NULL,
  `purchase_time` int(14) NOT NULL,
  `purchase_date` datetime NOT NULL,
  `sign` varchar(255) NOT NULL,
  `data` text,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mails
-- ----------------------------
DROP TABLE IF EXISTS `mails`;
CREATE TABLE `mails` (
  `mail_id` int(12) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `src_id` int(22) unsigned NOT NULL COMMENT '发送者',
  `dst_id` int(22) unsigned NOT NULL COMMENT '接收者',
  `type` enum('other','request','gift','text') NOT NULL DEFAULT 'text' COMMENT '类型',
  `content` varchar(512) NOT NULL DEFAULT '' COMMENT '内容',
  `when` int(12) NOT NULL DEFAULT '0' COMMENT '发送时间戳',
  `status` enum('none','deleted','accepted','read','unread') NOT NULL DEFAULT 'none' COMMENT '状态',
  PRIMARY KEY (`mail_id`),
  KEY `fk_mail_dst_id_account_id` (`dst_id`),
  CONSTRAINT `fk_mail_dst_id_account_id` FOREIGN KEY (`dst_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ranks
-- ----------------------------
DROP TABLE IF EXISTS `ranks`;
CREATE TABLE `ranks` (
  `account_id` int(22) unsigned NOT NULL,
  `win_0` bigint(22) unsigned NOT NULL DEFAULT '0' COMMENT '当前钱数',
  `win_1` bigint(22) unsigned NOT NULL DEFAULT '0' COMMENT '上次赢得钱数',
  `win_2` bigint(22) NOT NULL,
  `reward_tag` varchar(6) NOT NULL DEFAULT '000000' COMMENT '上次领取奖励的tag',
  PRIMARY KEY (`account_id`),
  KEY `idx_win` (`win_0`) USING BTREE,
  KEY `idx_last_win` (`win_1`),
  CONSTRAINT `fk_ranks_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for records
-- ----------------------------
DROP TABLE IF EXISTS `records`;
CREATE TABLE `records` (
  `record_id` int(22) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(22) unsigned NOT NULL DEFAULT '0' COMMENT '账户id',
  `src` varchar(512) NOT NULL DEFAULT '' COMMENT '操作员',
  `type` enum('other','diamond','chip') NOT NULL DEFAULT 'chip' COMMENT '类型',
  `amount` bigint(22) NOT NULL DEFAULT '0' COMMENT '数量',
  `code` int(11) NOT NULL DEFAULT '0' COMMENT '操作代码',
  `reason` varchar(512) NOT NULL DEFAULT '' COMMENT '操作原因',
  `when` int(12) NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tapjoy_points
-- ----------------------------
DROP TABLE IF EXISTS `tapjoy_points`;
CREATE TABLE `tapjoy_points` (
  `account_id` int(22) unsigned NOT NULL,
  `trans` int(12) unsigned NOT NULL,
  `when` datetime NOT NULL,
  `points` int(8) unsigned NOT NULL,
  PRIMARY KEY (`account_id`,`trans`),
  CONSTRAINT `fk_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
