CREATE TABLE `comments` (
`id` bigint(20) UNSIGNED NOT NULL,
`post_id` bigint(20) UNSIGNED DEFAULT NULL,
`author` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
`email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
`url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
`content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
`meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`status` enum('approved','unapproved','spam','deleted') NOT NULL DEFAULT 'unapproved',
`parent` smallint(5) UNSIGNED DEFAULT NULL,
`user_id` int(10) UNSIGNED DEFAULT NULL,
`minus` int(10) UNSIGNED DEFAULT NULL,
`plus` int(10) UNSIGNED DEFAULT NULL,
`type` varchar(50) NULL DEFAULT NULL,
`visitor_id` bigint(20) UNSIGNED DEFAULT NULL,
`datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `comments`
ADD PRIMARY KEY (`id`),
ADD KEY `comments_posts_id` (`post_id`) USING BTREE,
ADD KEY `comments_users_id` (`user_id`) USING BTREE,
ADD KEY `comments_visitors_id` (`visitor_id`);

ALTER TABLE `comments` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `comments`
ADD CONSTRAINT `comments_posts_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `comments_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
