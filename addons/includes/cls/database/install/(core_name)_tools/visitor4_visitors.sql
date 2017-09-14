/*
Navicat MySQL Data Transfer

Source Server         : Local
Source Server Version : 50505
Source Host           : localhost:3306

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-09 17:08:50
*/


-- ----------------------------
-- Table structure for visitors
-- ----------------------------
CREATE TABLE IF NOT EXISTS `visitors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` smallint(5) unsigned DEFAULT NULL,
  `visitor_ip` int(10) unsigned NOT NULL,
  `url_id` int(10) unsigned NOT NULL,
  `agent_id` int(10) unsigned NOT NULL,
  `url_idreferer` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `visitor_external` bit(1) DEFAULT NULL,
  `visitor_date` date NOT NULL,
  `visitor_time` time NOT NULL,
  `date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `visitorip_index` (`visitor_ip`) USING BTREE,
  KEY `url_id` (`url_id`),
  KEY `visitors_urls_referer` (`url_idreferer`),
  KEY `visitors_agents` (`agent_id`),
  KEY `visitors_services` (`service_id`),
  CONSTRAINT `visitors_agents` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `visitors_services` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `visitors_urls` FOREIGN KEY (`url_id`) REFERENCES `urls` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `visitors_urls_referer` FOREIGN KEY (`url_idreferer`) REFERENCES `urls` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
