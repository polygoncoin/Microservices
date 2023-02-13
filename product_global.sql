-- Adminer 4.8.1 MySQL 5.5.5-10.4.21-MariaDB-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `link_application_module`;
CREATE TABLE `link_application_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `link_crud_http`;
CREATE TABLE `link_crud_http` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `crud_id` int(11) NOT NULL,
  `http_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=332 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `link_crud_http_group`;
CREATE TABLE `link_crud_http_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_crud_http_id` int(11) DEFAULT NULL,
  `group_id` int(11) NOT NULL,
  `crud_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `link_application_module_group`;
CREATE TABLE `link_application_module_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_application_module_id` int(11) DEFAULT NULL,
  `group_id` int(11) NOT NULL,
  `crud_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `link_application_module`;
CREATE TABLE `link_application_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `master_application`;
CREATE TABLE `master_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bu_id` varchar(255) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `master_module`;
CREATE TABLE `master_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `master_bu`;
CREATE TABLE `master_bu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `master_bu_crud`;
CREATE TABLE `master_bu_crud` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bu_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `table` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `master_bu_group`;
CREATE TABLE `master_bu_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bi_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `master_http`;
CREATE TABLE `master_http` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
