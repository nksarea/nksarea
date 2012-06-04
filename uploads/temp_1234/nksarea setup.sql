-- Database setup `NKSArea`

CREATE TABLE `users` (
`id` INT (5) NOT NULL AUTO_INCREMENT,
`name` VARCHAR (20) NOT NULL,
`password` VARCHAR (64) NOT NULL,
`email` VARCHAR (1024) NOT NULL,
`realname` VARCHAR (128) NULL,
`access_level` INT (1) NOT NULL,
`class` INT (3) NULL,
`accept` INT (1) NOT NULL,
`last_activity` DATETIME NOT NULL,
`registrated` DATETIME NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`id`, `name`)
) ENGINE = MyISAM;

CREATE TABLE `classes` (
`id` INT (3) NOT NULL AUTO_INCREMENT,
`name` VARCHAR (6) NOT NULL,
`nickname` VARCHAR (24) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`id`, `nickname`)
) ENGINE = MyISAM;

CREATE TABLE `projects` (
`id` INT (10) NOT NULL AUTO_INCREMENT,
`owner` INT (5) NOT NULL,
`name` VARCHAR (64) NOT NULL,
`description` VARCHAR (2048) NULL,
`access_level` TEXT NOT NULL,
`list` INT (3) NOT NULL,
`upload_time` DATETIME NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`id`)
) ENGINE = MyISAM;

CREATE TABLE `lists` (
`id` INT (3) NOT NULL AUTO_INCREMENT,
`owner` INT (5) NOT NULL,
`name` VARCHAR (64) NOT NULL,
`creation_time` DATETIME NOT NULL,
`type` INT (1) NOT NULL,
`deadline` DATETIME NULL,
`class` INT (3) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`id`)
) ENGINE = MyISAM;

CREATE TABLE `registrations` (
`id` INT (5) NOT NULL AUTO_INCREMENT,
`hash` VARCHAR (64) NOT NULL,
`type` INT (1) NOT NULL,
`valid_until` DATETIME NOT NULL,
`registrator` INT (5) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`id`)
) ENGINE = MyISAM;

