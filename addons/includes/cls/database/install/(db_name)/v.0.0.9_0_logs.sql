CREATE TABLE `logs` (
`id` bigint(20) UNSIGNED NOT NULL,
`logitem_id` smallint(5) UNSIGNED NOT NULL,
`user_id` int(10) UNSIGNED DEFAULT NULL,
`data` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
`meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`status` enum('enable','disable','expire','deliver') DEFAULT NULL,
`desc` varchar(250) DEFAULT NULL,
`createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
`date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `logs`
ADD PRIMARY KEY (`id`),
ADD KEY `logs_users_id` (`user_id`) USING BTREE,
ADD KEY `logs_logitems_id` (`logitem_id`) USING BTREE;

ALTER TABLE `logs` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `logs`
ADD CONSTRAINT `logs_logitems_id` FOREIGN KEY (`logitem_id`) REFERENCES `logitems` (`id`) ON UPDATE CASCADE,
ADD CONSTRAINT `logs_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
