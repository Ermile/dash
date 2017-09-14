CREATE TABLE `termusages` (
`term_id` int(10) UNSIGNED NOT NULL,
`related_id` bigint(20) UNSIGNED NOT NULL,
`related` enum('posts','products','attachments','files','comments','users') DEFAULT NULL,
`order` smallint(5) UNSIGNED DEFAULT NULL,
`status` enum('enable','disable','expired','awaiting','filtered','blocked','spam','violence','pornography','other','deleted') NOT NULL DEFAULT 'enable',
`createdate` datetime DEFAULT CURRENT_TIMESTAMP,
`date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`type` enum('cat','tag','term','code','other','barcode1','barcode2','barcode3','qrcode1','qrcode2','qrcode3','rfid1','rfid2','rfid3','fingerprint1','fingerprint2','fingerprint3','fingerprint4','fingerprint5','fingerprint6','fingerprint7','fingerprint8','fingerprint9','fingerprint10') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `termusages`
  ADD KEY `term_id` (`term_id`),
  ADD KEY `related_id` (`related_id`),
  ADD KEY `related` (`related`),
  ADD KEY `status` (`status`);
