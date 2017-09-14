/*
Navicat MySQL Data Transfer

Source Server         : Local
Source Server Version : 50505
Source Host           : localhost:3306

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-09 17:57:57
*/


-- ----------------------------
-- Table structure for agents
-- ----------------------------
CREATE TABLE IF NOT EXISTS `agents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_agent` text NOT NULL,
  `agent_group` varchar(50) DEFAULT NULL,
  `agent_name` varchar(50) DEFAULT NULL,
  `agent_version` varchar(50) DEFAULT NULL,
  `agent_os` varchar(50) DEFAULT NULL,
  `agent_osnum` varchar(50) DEFAULT NULL,
  `agent_robot` bit(1) DEFAULT NULL,
  `agent_meta` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
