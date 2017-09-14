
CREATE TABLE `logitems` (
`id` smallint(5) UNSIGNED NOT NULL,
`type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
`caller` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
`title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
`desc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
`meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`count` int(10) UNSIGNED NOT NULL DEFAULT '0',
`priority` enum('critical','high','medium','low') NOT NULL DEFAULT 'medium',
`datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `logitems` ADD PRIMARY KEY (`id`);
ALTER TABLE `logitems` MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;