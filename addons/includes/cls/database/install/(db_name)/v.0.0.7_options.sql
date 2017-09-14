CREATE TABLE `options` (
`id` bigint(20) UNSIGNED NOT NULL,
`user_id` int(10) UNSIGNED DEFAULT NULL,
`post_id` bigint(20) UNSIGNED DEFAULT NULL,
`parent_id` bigint(20) UNSIGNED DEFAULT NULL,
`cat` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
`key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
`value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
`meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`status` enum('enable','disable','expire') NOT NULL DEFAULT 'enable',
`datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `options`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `cat+key+value` (`cat`,`key`,`value`) USING BTREE,
ADD KEY `options_users_id` (`user_id`),
ADD KEY `options_posts_id` (`post_id`),
ADD KEY `options_parent_id` (`parent_id`);


ALTER TABLE `options` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

ALTER TABLE `options`
ADD CONSTRAINT `options_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `options` (`id`) ON UPDATE CASCADE,
ADD CONSTRAINT `options_posts_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `options_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;