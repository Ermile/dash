CREATE TABLE `posts` (
`id` bigint(20) UNSIGNED NOT NULL,
`language` char(2) DEFAULT NULL,
`title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
`slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
`url` varchar(255) NOT NULL,
`content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'post',
`comment` enum('open','closed') DEFAULT NULL,
`count` smallint(5) UNSIGNED DEFAULT NULL,
`order` int(10) UNSIGNED DEFAULT NULL,
`status` enum('publish','draft','schedule','deleted','expire') NOT NULL DEFAULT 'draft',
`parent` bigint(20) UNSIGNED DEFAULT NULL,
`user_id` int(10) UNSIGNED NOT NULL,
`publishdate` datetime DEFAULT NULL,
`datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `posts`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `url_unique` (`url`,`language`) USING BTREE,
ADD KEY `posts_users_id` (`user_id`) USING BTREE;


ALTER TABLE `posts`
MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


ALTER TABLE `posts`
ADD CONSTRAINT `posts_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
