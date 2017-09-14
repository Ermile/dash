CREATE TABLE `users` (
`id` int(10) UNSIGNED NOT NULL,
`mobile` varchar(15) DEFAULT NULL,
`email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
`password` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
`displayname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
`meta` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
`status` enum('active','awaiting','deactive','removed','filter','unreachable') DEFAULT 'awaiting',
`parent` int(10) UNSIGNED DEFAULT NULL,
`permission` varchar(1000) DEFAULT NULL,
`type` varchar(100) DEFAULT NULL,
`datecreated` datetime  NULL DEFAULT CURRENT_TIMESTAMP,
`datemodified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`username` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
`group` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
`fileid` int(20) UNSIGNED DEFAULT NULL,
`chatid` int(20) UNSIGNED DEFAULT NULL,
`pin` smallint(4) UNSIGNED DEFAULT NULL,
`ref` int(10) UNSIGNED DEFAULT NULL,
`creator` int(10) UNSIGNED DEFAULT NULL,
`twostep` bit(1) DEFAULT NULL,
`googlemail` varchar(100) DEFAULT NULL,
`facebookmail` varchar(100) DEFAULT NULL,
`twittermail` varchar(100) DEFAULT NULL,
`dontwillsetmobile` varchar(50) DEFAULT NULL,
`fileurl` varchar(2000) DEFAULT NULL,
`notification` text,
`setup` bit(1) DEFAULT NULL,
`name` varchar(100) DEFAULT NULL,
`lastname` varchar(100) DEFAULT NULL,
`father` varchar(100) DEFAULT NULL,
`birthday` datetime DEFAULT NULL,
`shcode` varchar(100) DEFAULT NULL,
`nationalcode` varchar(100) DEFAULT NULL,
`shfrom` varchar(100) DEFAULT NULL,
`nationality` varchar(100) DEFAULT NULL,
`brithplace` varchar(100) DEFAULT NULL,
`region` varchar(100) DEFAULT NULL,
`passportcode` varchar(100) DEFAULT NULL,
`marital` enum('single','married') DEFAULT NULL,
`gender` enum('male','female') DEFAULT NULL,
`childcount` smallint(2) DEFAULT NULL,
`education` varchar(100) DEFAULT NULL,
`insurancetype` varchar(100) DEFAULT NULL,
`insurancecode` varchar(100) DEFAULT NULL,
`dependantscount` smallint(4) DEFAULT NULL,
`postion` varchar(100) DEFAULT NULL,
`unit_id` smallint(5) DEFAULT NULL,
`language` char(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `users` ADD PRIMARY KEY (`id`);

ALTER TABLE `users` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;


INSERT INTO `users` (`id`, `mobile`, `email`, `password`, `displayname`, `meta`, `status`, `permission`, `datecreated`, `datemodified`) VALUES
(1, '989357269759', 'J.Evazzadeh@gmail.com', '$2y$07$9wj8/jDeQKyY0t0IcUf.xOEy98uf6BaSS7Tg28swrKUDxdKzUVfsy', 'Javad Evazzadeh', NULL, 'active', 'admin', '2015-01-01 00:00:00', NULL),
(2, '989356032043', 'itb.baravak@gmail.com', '$2y$07$ZRUphEsEn9bK8inKBfYt.efVoZDgBaoNfZz0uVRqRGvH9.che.Bqq', 'Hasan Salehi', NULL, 'active', NULL, '2015-01-02 00:00:00', NULL),
(3, '989190499033', 'ahmadkarimi1991@gmail.com', '$2y$07$bLbhODUiPBFfbTU8V./m5OAYdkH2DP7uCQI2fVLubq7X/LdFQTeH.', 'Ahmad Karimi', NULL, 'active', NULL, '2015-01-03 00:00:00', NULL),
(4, '989109610612', 'rm.biqarar@gmail.com', '$2y$07$k.Vi7QCpdym637.6rwbm2.u1tdMi4jyWFUg7YgNv.XnBFOP1.7W/y', 'Reza Mohiti', NULL, 'active', NULL, '2015-01-04 00:00:00', NULL);

