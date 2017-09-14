CREATE TABLE `userparents` (
`id` bigint(20) UNSIGNED NOT NULL,
`user_id` int(10) UNSIGNED NOT NULL,
`parent` int(10) UNSIGNED NOT NULL,
`creator` int(10) UNSIGNED DEFAULT NULL,
`level` smallint(5) DEFAULT NULL,
`status` enum('enable','disable','expire','deleted') DEFAULT 'enable',
`title` enum('father','mother','sister','brother','grandfather','grandmother','aunt','husband of the aunt','uncle','boy','girl','spouse','stepmother','stepfather','neighbor','teacher','friend','boss','supervisor','child','grandson','custom') DEFAULT NULL,
`othertitle` varchar(255) DEFAULT NULL,
`createdate` datetime DEFAULT CURRENT_TIMESTAMP,
`date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`desc` text CHARACTER SET utf8mb4,
`meta` mediumtext CHARACTER SET utf8mb4
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `userparents`
ADD PRIMARY KEY (`id`),
ADD KEY `userparents_users_id` (`user_id`),
ADD KEY `userparents_creator` (`creator`),
ADD KEY `userparents_parent` (`parent`);

ALTER TABLE `userparents` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `userparents`
ADD CONSTRAINT `userparents_creator` FOREIGN KEY (`creator`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `userparents_parent` FOREIGN KEY (`parent`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `userparents_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
