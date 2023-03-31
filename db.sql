-- Adminer 4.8.1 MySQL 5.5.5-10.4.21-MariaDB-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `l001_link_allowed_crud`;
CREATE TABLE `l001_link_allowed_crud` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `crud_id` int(11) DEFAULT NULL,
  `module_id` int(11) DEFAULT NULL,
  `http_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `id` (`id`),
  KEY `crud_id` (`crud_id`),
  CONSTRAINT `l001_link_allowed_crud_ibfk_1` FOREIGN KEY (`crud_id`) REFERENCES `l003_link_allowed_crud_details` (`corporate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `l002_link_allowed_route`;
CREATE TABLE `l002_link_allowed_route` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `route_id` int(11) DEFAULT NULL,
  `module_id` int(11) DEFAULT NULL,
  `http_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `id` (`id`),
  KEY `crud_id` (`route_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `l003_link_allowed_crud_details`;
CREATE TABLE `l003_link_allowed_crud_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `corporate_id` int(11) NOT NULL,
  PRIMARY KEY (`corporate_id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `m000_master_group`;
CREATE TABLE `m000_master_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `group_allowed_ips` text DEFAULT NULL,
  `corporate_id` int(11) NOT NULL,
  `corporate_allowed_ips` text DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `company_allowed_ips` text DEFAULT NULL,
  `application_id` int(11) NOT NULL,
  `application_allowed_ips` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m000_master_group` (`id`, `name`, `comment`, `group_allowed_ips`, `corporate_id`, `corporate_allowed_ips`, `company_id`, `company_allowed_ips`, `application_id`, `application_allowed_ips`) VALUES
(1,	'Polygon Super Admin',	'',	NULL,	0,	NULL,	0,	NULL,	0,	NULL);

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
  `corporate_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m002_master_company` (`id`, `corporate_id`, `name`, `comment`, `allowed_ips`) VALUES
(1,	0,	'polygon.co.in',	'',	'');

DROP TABLE IF EXISTS `m003_master_application`;
CREATE TABLE `m003_master_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  `db_id` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m003_master_application` (`id`, `company_id`, `name`, `comment`, `allowed_ips`, `db_id`) VALUES
(1,	0,	'PolygonMicroservices',	'',	'',	NULL);

DROP TABLE IF EXISTS `m004_master_client`;
CREATE TABLE `m004_master_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `database` varchar(255) NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m004_master_client` (`id`, `application_id`, `name`, `comment`, `database`, `allowed_ips`) VALUES
(1,	0,	'All',	'',	'',	'');

DROP TABLE IF EXISTS `m005_master_module`;
CREATE TABLE `m005_master_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m005_master_module` (`id`, `client_id`, `name`, `comment`, `allowed_ips`) VALUES
(1,	0,	'All',	'',	'');

DROP TABLE IF EXISTS `m006_master_crud`;
CREATE TABLE `m006_master_crud` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `table` varchar(255) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `db_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m006_master_crud` (`id`, `name`, `table`, `application_id`, `db_id`) VALUES
(1,	'user',	'/crud/m008_master_user',	NULL,	NULL);

DROP TABLE IF EXISTS `m007_master_http`;
CREATE TABLE `m007_master_http` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `m007_master_http` (`id`, `name`, `description`) VALUES
(1,	'GET',	''),
(2,	'POST',	''),
(3,	'PUT',	''),
(4,	'DELETE',	'');

DROP TABLE IF EXISTS `m008_master_db`;
CREATE TABLE `m008_master_db` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) NOT NULL,
  `database` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m008_master_db` (`id`, `hostname`, `database`, `description`) VALUES
(1,	'localhost',	'product_global',	'');

DROP TABLE IF EXISTS `m009_master_route`;
CREATE TABLE `m009_master_route` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `application_id` int(11) NOT NULL,
  `db_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m009_master_route` (`id`, `name`, `route`, `application_id`, `db_id`) VALUES
(1,	'user',	'/crud/m008_master_user',	0,	0);

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `m010_master_user`;
CREATE TABLE `m010_master_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `group_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`group_ids`)),
  `allowed_ips` text DEFAULT NULL,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `m010_master_user` (`id`, `username`, `password_hash`, `group_ids`, `allowed_ips`, `updated_by`, `created_by`, `updated_on`, `created_on`, `is_disabled`, `is_deleted`) VALUES
(1,	'shames11@rediffmail.com',	'$2y$10$o8hFTjBIXQS.fOED2Ut1ZOCSdDjTnS3lyELI4rWyFEnu4GUyJr3O6',	'1',	NULL,	0,	0,	'2023-03-01 08:31:57',	'2023-02-22 09:42:50',	'No',	'No');

-- 2023-03-31 06:59:23
