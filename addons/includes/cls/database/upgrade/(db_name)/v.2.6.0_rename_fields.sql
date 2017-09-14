ALTER TABLE `posts` CHANGE `post_language` `language` char(2) DEFAULT NULL;
ALTER TABLE `posts` CHANGE `post_title` `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `posts` CHANGE `post_slug` `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `posts` CHANGE `post_url` `url` varchar(255) NOT NULL;
ALTER TABLE `posts` CHANGE `post_content` `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `posts` CHANGE `post_meta` `meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `posts` CHANGE `post_type` `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'post';
ALTER TABLE `posts` CHANGE `post_comment` `comment` enum('open','closed') DEFAULT NULL;
ALTER TABLE `posts` CHANGE `post_count` `count` smallint(5) UNSIGNED DEFAULT NULL;
ALTER TABLE `posts` CHANGE `post_order` `order` int(10) UNSIGNED DEFAULT NULL;
ALTER TABLE `posts` CHANGE `post_status` `status` enum('publish','draft','schedule','deleted','expire') NOT NULL DEFAULT 'draft';
ALTER TABLE `posts` CHANGE `post_parent` `parent` bigint(20) UNSIGNED DEFAULT NULL;
ALTER TABLE `posts` CHANGE `post_publishdate` `publishdate` datetime DEFAULT NULL;

ALTER TABLE `posts` CHANGE `date_modified` `datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `posts` ADD `datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP;


ALTER TABLE `comments` CHANGE `comment_author` `author` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL;
ALTER TABLE `comments` CHANGE `comment_email` `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL;
ALTER TABLE `comments` CHANGE `comment_url` `url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL;
ALTER TABLE `comments` CHANGE `comment_content` `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;
ALTER TABLE `comments` CHANGE `comment_meta` `meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `comments` CHANGE `comment_status` `status` enum('approved','unapproved','spam','deleted') NOT NULL DEFAULT 'unapproved';
ALTER TABLE `comments` CHANGE `comment_parent` `parent` bigint(20) UNSIGNED DEFAULT NULL;
ALTER TABLE `comments` CHANGE `comment_minus` `minus` int(10) UNSIGNED DEFAULT NULL;
ALTER TABLE `comments` CHANGE `comment_plus` `plus` int(10) UNSIGNED DEFAULT NULL;
ALTER TABLE `comments` CHANGE `comment_type` `type` varchar(50) NULL DEFAULT NULL;

ALTER TABLE `comments` CHANGE `date_modified` `datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `comments` ADD `datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP;


ALTER TABLE `options` CHANGE `option_cat` `cat` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `options` CHANGE `option_key` `key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `options` CHANGE `option_value` `value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL;
ALTER TABLE `options` CHANGE `option_meta` `meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `options` CHANGE `option_status` `status` enum('enable','disable','expire') NOT NULL DEFAULT 'enable';
ALTER TABLE `options` CHANGE `date_modified` `datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `options` ADD `datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP;



ALTER TABLE `logitems` CHANGE `logitem_type` `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL;
ALTER TABLE `logitems` CHANGE `logitem_caller` `caller` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `logitems` CHANGE `logitem_title` `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `logitems` CHANGE `logitem_desc` `desc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL;
ALTER TABLE `logitems` CHANGE `logitem_meta` `meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `logitems` CHANGE `logitem_priority` `priority` enum('critical','high','medium','low') NOT NULL DEFAULT 'medium';
ALTER TABLE `logitems` CHANGE `date_modified` `datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `logitems` ADD `datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP;


ALTER TABLE `logs` CHANGE `log_data` `data` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL;
ALTER TABLE `logs` CHANGE `log_meta` `meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `logs` CHANGE `log_status` `status` enum('enable','disable','expire','deliver') DEFAULT NULL;
ALTER TABLE `logs` CHANGE `log_createdate` `datecreated` datetime  NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `logs` CHANGE `date_modified` `datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `logs` CHANGE `log_desc` `desc` varchar(250) DEFAULT NULL;

