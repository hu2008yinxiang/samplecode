/*
Navicat MySQL Data Transfer

Source Server         : localhost_13306
Source Server Version : 50537
Source Host           : localhost:13306
Source Database       : crash_log

Target Server Type    : MYSQL
Target Server Version : 50537
File Encoding         : 65001

Date: 2014-05-14 17:50:34
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `crash_records`
-- ----------------------------
DROP TABLE IF EXISTS `crash_records`;
CREATE TABLE `crash_records` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` varchar(255) NOT NULL DEFAULT 'unkown',
  `versionCode` int(11) NOT NULL DEFAULT '0',
  `model` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `androidSdkInt` int(11) DEFAULT '0',
  `versionName` varchar(255) DEFAULT NULL,
  `status` enum('debug','release') DEFAULT 'release',
  `proc` varchar(512) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `time_stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archieve` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
