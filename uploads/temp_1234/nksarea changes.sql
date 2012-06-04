ALTER TABLE `users` CHANGE `name` `name` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE `users` CHANGE `registrated` `registrated` DATETIME NULL;
-- Ab 12.05.11
ALTER TABLE `projects` CHANGE `list` `list` INT( 3 ) NULL;