CREATE TABLE `transactions` (
`id` bigint(20) UNSIGNED NOT NULL,
`user_id` int(10) UNSIGNED NOT NULL,
`code` smallint(5) NOT NULL,
`title` varchar(255) NOT NULL,
`caller` varchar(100) DEFAULT NULL,
`type` enum('gift','prize','transfer','promo','money') NOT NULL,
`unit_id` smallint(3) NOT NULL,
`amount_request` bigint(20) DEFAULT NULL,
`amount_end` bigint(20) DEFAULT NULL,
`plus` bigint(20) UNSIGNED DEFAULT NULL,
`minus` bigint(20) UNSIGNED DEFAULT NULL,
`budget_before` bigint(20) DEFAULT NULL,
`budget` bigint(20) DEFAULT NULL,
`status` enum('enable','disable','deleted','expired','awaiting','filtered','blocked','spam') NOT NULL DEFAULT 'enable',
`condition` enum('request','redirect','cancel','pending','error','verify_request','verify_error','ok') DEFAULT NULL,
`verify` bit(1) NOT NULL DEFAULT b'0',
`parent_id` bigint(20) UNSIGNED DEFAULT NULL,
`related_user_id` int(10) UNSIGNED DEFAULT NULL,
`related_foreign` varchar(50) DEFAULT NULL,
`related_id` bigint(20) UNSIGNED DEFAULT NULL,
`payment` varchar(50) DEFAULT NULL,
`payment_response` text CHARACTER SET utf8mb4,
`meta` mediumtext CHARACTER SET utf8mb4,
`desc` text CHARACTER SET utf8mb4,
`createdate` datetime DEFAULT CURRENT_TIMESTAMP,
`date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`invoice_id` int(10) UNSIGNED DEFAULT NULL,
`date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `transactions`
ADD PRIMARY KEY (`id`),
ADD KEY `newtransactions_user_id` (`user_id`);


ALTER TABLE `transactions` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `transactions` ADD CONSTRAINT `newtransactions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
