CREATE TABLE `socials` (
`id` bigint(20) UNSIGNED NOT NULL,
`code` varchar(255) DEFAULT NULL,
`social` varchar(50) NOT NULL,
`user_id` int(10) UNSIGNED NOT NULL,
`status` enum('enable','expire','disable') NOT NULL DEFAULT 'enable',
`email` varchar(255) DEFAULT NULL,
`verified` bit(1) DEFAULT NULL,
`name` varchar(255) DEFAULT NULL,
`family` varchar(255) DEFAULT NULL,
`displayname` varchar(500) DEFAULT NULL,
`gender` varchar(255) DEFAULT NULL,
`language` varchar(50) DEFAULT NULL,
`picture` varchar(1000) DEFAULT NULL,
`hd` varchar(500) DEFAULT NULL,
`link` varchar(1000) DEFAULT NULL,
`desc` text,
`meta` mediumtext,
`createdate` datetime DEFAULT CURRENT_TIMESTAMP,
`date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `socials`
ADD PRIMARY KEY (`id`),
ADD KEY `social_user_id` (`user_id`);

ALTER TABLE `socials` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `socials` ADD CONSTRAINT `social_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
