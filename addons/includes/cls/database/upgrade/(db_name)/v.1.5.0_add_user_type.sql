ALTER TABLE `users` ADD `type` varchar(100) DEFAULT NULL;
ALTER TABLE `users` ADD `job` varchar(100) DEFAULT NULL;
ALTER TABLE `users` ADD `cardnumber` varchar(100) DEFAULT NULL;
ALTER TABLE `users` ADD `shaba` varchar(100) DEFAULT NULL;
ALTER TABLE `users` ADD `personnelcode` varchar(100) DEFAULT NULL;
ALTER TABLE `users` CHANGE `birthday` `birthday` varchar(50) DEFAULT NULL;
ALTER TABLE `users` CHANGE `datecreated` `datecreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `users` ADD `passportexpire` varchar(100) DEFAULT NULL;
ALTER TABLE `users` ADD `paymentaccountnumber` varchar(100) DEFAULT NULL;

ALTER TABLE `users` CHANGE `pasportcode` `passportcode` varchar(100) DEFAULT NULL;

ALTER TABLE `users` CHANGE `marital` `marital` enum('single','married', 'marride') DEFAULT NULL;
UPDATE users SET users.marital = 'married' WHERE users.marital = 'marride';
ALTER TABLE `users` CHANGE `marital` `marital` enum('single','married') DEFAULT NULL;