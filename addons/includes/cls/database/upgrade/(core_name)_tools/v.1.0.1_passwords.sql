CREATE TABLE IF NOT EXISTS `passwords` (
`id` 			bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`password` 		varchar(255) NOT NULL,
`status` 		ENUM('normal','common','very_common','crazy') NOT NULL DEFAULT 'normal',
`substatus`		varchar(50) NULL,
`try` 			bigint(20) unsigned NOT NULL DEFAULT 0,
`wrong` 		bigint(20) unsigned NOT NULL DEFAULT 0,
`used` 			bigint(20) unsigned NOT NULL DEFAULT 0,
`createdate` 	TIMESTAMP NULL DEFAULT NULL,
`datemodified` 	TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
UNIQUE KEY `unique` (`password`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;