CREATE TABLE `invoice_details` (
`id` int(10) UNSIGNED NOT NULL,
`invoice_id` int(10) UNSIGNED NOT NULL,
`title` varchar(500) NOT NULL,
`price` int(10) DEFAULT NULL,
`count` smallint(5) DEFAULT NULL,
`total` int(10) DEFAULT NULL,
`discount` smallint(5) DEFAULT NULL,
`status` enum('enable','disable','expire') NOT NULL DEFAULT 'enable',
`createdate` datetime DEFAULT CURRENT_TIMESTAMP,
`date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`meta` mediumtext CHARACTER SET utf8mb4
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `invoice_details`
ADD PRIMARY KEY (`id`),
ADD KEY `inovoices_id` (`invoice_id`);

ALTER TABLE `invoice_details` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `invoice_details` ADD CONSTRAINT `inovoices_id` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON UPDATE CASCADE;
