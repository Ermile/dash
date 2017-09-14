CREATE TABLE IF NOT EXISTS `words` (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`word` varchar(50) NOT NULL,
`status` ENUM('enable','disable','expired','awaiting','filtered','blocked','spam','violence','pornography','other') NOT NULL DEFAULT 'enable',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;