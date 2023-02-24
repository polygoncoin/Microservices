-- Adminer 4.8.1 MySQL 5.5.5-10.4.21-MariaDB-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `m000_master_group`;
CREATE TABLE `m000_master_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m000_master_group` (`id`, `name`, `comment`, `allowed_ips`) VALUES
(1,	'Polygon Super Admin',	'',	NULL);

DROP TABLE IF EXISTS `m001_master_corporate`;
CREATE TABLE `m001_master_corporate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m001_master_corporate` (`id`, `name`, `comment`, `allowed_ips`) VALUES
(1,	'polygon',	'',	'');

DROP TABLE IF EXISTS `m002_master_company`;
CREATE TABLE `m002_master_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m002_master_company` (`id`, `name`, `comment`, `allowed_ips`) VALUES
(1,	'polygon.co.in',	'',	'');

DROP TABLE IF EXISTS `m003_master_application`;
CREATE TABLE `m003_master_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m003_master_application` (`id`, `name`, `comment`, `allowed_ips`) VALUES
(1,	'PolygonMicroservices',	'',	'');

DROP TABLE IF EXISTS `m004_master_module`;
CREATE TABLE `m004_master_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m004_master_module` (`id`, `name`, `comment`, `allowed_ips`) VALUES
(1,	'All',	'',	'');

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

DROP TABLE IF EXISTS `m007_master_route`;
CREATE TABLE `m007_master_route` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `group_id` int(11) NOT NULL,
  `corporate_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `http_id` int(11) NOT NULL,
  `db_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `m008_master_user`;
CREATE TABLE `m008_master_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `group_id` int(11) NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m008_master_user` (`id`, `username`, `password_hash`, `group_id`, `allowed_ips`, `updated_by`, `created_by`, `updated_on`, `created_on`, `is_disabled`, `is_deleted`) VALUES
(1,	'shames11@rediffmail.com',	'$2y$10$o8hFTjBIXQS.fOED2Ut1ZOCSdDjTnS3lyELI4rWyFEnu4GUyJr3O6',	0,	NULL,	0,	0,	'2023-02-22 09:42:50',	'2023-02-22 09:42:50',	'No',	'No');

DROP TABLE IF EXISTS `m009_master_client`;
CREATE TABLE `m009_master_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m009_master_client` (`id`, `name`, `description`, `allowed_ips`) VALUES
(1,	'Ramesh',	'',	'');

DROP TABLE IF EXISTS `m010_master_db`;
CREATE TABLE `m010_master_db` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) NOT NULL,
  `database` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m010_master_db` (`id`, `hostname`, `database`) VALUES
(1,	'localhost',	'product_global');

-- 2023-02-24 13:35:20
