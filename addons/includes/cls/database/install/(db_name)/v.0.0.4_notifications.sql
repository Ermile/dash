CREATE TABLE `notifications` (
`id` bigint(20) UNSIGNED NOT NULL,
`user_id` int(10) UNSIGNED NOT NULL,
`user_idsender` int(10) UNSIGNED DEFAULT NULL,
`title` varchar(500) CHARACTER SET utf8mb4 DEFAULT NULL,
`content` text CHARACTER SET utf8mb4,
`url` varchar(2000) CHARACTER SET utf8mb4 DEFAULT NULL,
`read` bit(1) DEFAULT NULL,
`star` bit(1) DEFAULT NULL,
`status` enum('awaiting','enable','disable','expire','deleted','cancel','block') DEFAULT NULL,
`category` smallint(5) DEFAULT NULL,
`createdate` datetime DEFAULT CURRENT_TIMESTAMP,
`expiredate` datetime DEFAULT NULL,
`readdate` datetime DEFAULT NULL,
`date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`desc` text CHARACTER SET utf8mb4,
`meta` mediumtext CHARACTER SET utf8mb4,
`telegram` bit(1) DEFAULT NULL,
`telegramdate` datetime DEFAULT NULL,
`sms` bit(1) DEFAULT NULL,
`smsdate` datetime DEFAULT NULL,
`smsdeliverdate` datetime DEFAULT NULL,
`email` bit(1) DEFAULT NULL,
`emaildate` datetime DEFAULT NULL,
`related_foreign` varchar(50) DEFAULT NULL,
`related_id` bigint(20) UNSIGNED DEFAULT NULL,
`needanswer` bit(1) DEFAULT NULL,
`answer` smallint(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `notifications`
ADD PRIMARY KEY (`id`),
ADD KEY `notifications_users_idsender` (`user_idsender`),
ADD KEY `user_id` (`user_id`);

ALTER TABLE `notifications` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `notifications`
ADD CONSTRAINT `notifications_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `notifications_users_idsender` FOREIGN KEY (`user_idsender`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
