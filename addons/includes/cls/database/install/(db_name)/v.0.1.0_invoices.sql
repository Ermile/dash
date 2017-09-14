CREATE TABLE `invoices` (
`id` int(10) UNSIGNED NOT NULL,
`date` datetime NOT NULL,
`user_id_seller` int(10) UNSIGNED DEFAULT NULL,
`user_id` int(10) UNSIGNED NOT NULL,
`temp` bit(1) DEFAULT NULL,
`title` varchar(500) NOT NULL,
`total` bigint(20) DEFAULT NULL,
`total_discount` int(10) DEFAULT NULL,
`status` enum('enable','disable','expire') NOT NULL DEFAULT 'enable',
`date_pay` datetime DEFAULT NULL,
`transaction_bank` varchar(255) DEFAULT NULL,
`discount` int(10) DEFAULT NULL,
`vat` int(10) DEFAULT NULL,
`vat_pay` int(10) DEFAULT NULL,
`final_total` bigint(20) DEFAULT NULL,
`count_detail` smallint(5) UNSIGNED DEFAULT NULL,
`createdate` datetime DEFAULT CURRENT_TIMESTAMP,
`date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`meta` mediumtext CHARACTER SET utf8mb4
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `invoices`
ADD PRIMARY KEY (`id`),
ADD KEY `inovoices_user_id` (`user_id`),
ADD KEY `inovoices_user_id_seller` (`user_id_seller`);

ALTER TABLE `invoices` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `invoices`
ADD CONSTRAINT `inovoices_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
ADD CONSTRAINT `inovoices_user_id_seller` FOREIGN KEY (`user_id_seller`) REFERENCES `users` (`id`) ON UPDATE CASCADE;