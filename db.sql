-- Adminer 4.8.1 MySQL 5.5.5-10.4.21-MariaDB-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `l000_link`;
CREATE TABLE `l000_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `corporate_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `application_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `module_id` int(11) DEFAULT NULL,
  `api_type` enum('crudapi','customapi') DEFAULT NULL,
  `api_id` int(11) DEFAULT NULL,
  `db_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `m000_master_corporate`;
CREATE TABLE `m000_master_corporate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m000_master_corporate` (`id`, `name`, `comment`) VALUES
(1,	'polygon',	'');

DROP TABLE IF EXISTS `m001_master_company`;
CREATE TABLE `m001_master_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m001_master_company` (`id`, `name`, `comment`) VALUES
(1,	'polygon',	'');

DROP TABLE IF EXISTS `m002_master_group`;
CREATE TABLE `m002_master_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m002_master_group` (`id`, `name`, `comment`) VALUES
(1,	'Super admin',	'');

DROP TABLE IF EXISTS `m003_master_application`;
CREATE TABLE `m003_master_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m003_master_application` (`id`, `name`, `comment`) VALUES
(1,	'Microservices',	'');

DROP TABLE IF EXISTS `m004_master_module`;
CREATE TABLE `m004_master_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m004_master_module` (`id`, `name`, `comment`) VALUES
(1,	'api',	'');

DROP TABLE IF EXISTS `m005_master_http`;
CREATE TABLE `m005_master_http` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `m005_master_http` (`id`, `name`, `description`) VALUES
(1,	'GET',	''),
(2,	'POST',	''),
(3,	'PUT',	''),
(4,	'DELETE',	'');

DROP TABLE IF EXISTS `m006_master_crudapi`;
CREATE TABLE `m006_master_crudapi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `table` varchar(255) NOT NULL,
  `http_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m006_master_crudapi` (`id`, `name`, `table`, `http_id`) VALUES
(1,	'bu',	'master_bu',	0),
(2,	'crud',	'master_crudapi',	0),
(3,	'customapi',	'master_customapi',	0),
(4,	'group',	'master_group',	0),
(5,	'http',	'master_http',	0),
(6,	'module',	'master_module',	0);

DROP TABLE IF EXISTS `m007_master_customapi`;
CREATE TABLE `m007_master_customapi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `http_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `m008_master_user`;
CREATE TABLE `m008_master_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m008_master_user` (`id`, `username`, `password_hash`, `updated_by`, `created_by`, `updated_on`, `created_on`, `disabled`, `deleted`) VALUES
(1,	'shames11@rediffmail.com',	'$2y$10$o8hFTjBIXQS.fOED2Ut1ZOCSdDjTnS3lyELI4rWyFEnu4GUyJr3O6',	0,	0,	'2023-02-22 09:42:50',	'2023-02-22 09:42:50',	'No',	'No');

DROP TABLE IF EXISTS `m009_master_client`;
CREATE TABLE `m009_master_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `m010_master_db`;
CREATE TABLE `m010_master_db` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) NOT NULL,
  `database` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- 2023-02-22 15:02:28
