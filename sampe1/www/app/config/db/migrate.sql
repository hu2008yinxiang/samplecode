ALTER TABLE `positions`
MODIFY COLUMN `latitude`  float(5,2) NULL DEFAULT NULL AFTER `account_id`,
MODIFY COLUMN `longitude`  float(5,2) NULL DEFAULT NULL AFTER `latitude`,
ADD INDEX `index_lat` (`latitude`) USING BTREE,
ADD INDEX `index_lon1` (`longitude`) USING BTREE;
