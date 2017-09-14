CREATE TABLE if not exists `sessions` (
	`id` varchar(32) NOT NULL,
	`session_name` varchar(32) NOT NULL,
	`session_create` datetime NOT NULL,
	`session_expire` datetime NOT NULL,
	`session_data` text,
	`session_meta` mediumtext,
	`datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;