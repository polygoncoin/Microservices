-- MySQL dump 10.13  Distrib 8.0.32, for macos12.6 (x86_64)
--
-- Host: localhost    Database: global
-- ------------------------------------------------------
-- Server version	8.0.32

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `l001_link_allowed_route`
--

DROP TABLE IF EXISTS `l001_link_allowed_route`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `l001_link_allowed_route` (
  `link_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `route_id` int DEFAULT NULL,
  `http_id` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` int DEFAULT NULL,
  `approved_on` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_approved` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`link_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m001_master_group`
--

DROP TABLE IF EXISTS `m001_master_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m001_master_group` (
  `group_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `client_id` int DEFAULT NULL,
  `connection_id` int NOT NULL,
  `allowed_ips` text,
  `comments` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` int DEFAULT NULL,
  `approved_on` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_approved` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m002_master_user`
--

DROP TABLE IF EXISTS `m002_master_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m002_master_user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `group_id` int NOT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` int DEFAULT NULL,
  `approved_on` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_approved` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m003_master_route`
--

DROP TABLE IF EXISTS `m003_master_route`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m003_master_route` (
  `route_id` int NOT NULL AUTO_INCREMENT,
  `route` varchar(255) NOT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` int DEFAULT NULL,
  `approved_on` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_approved` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`route_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m004_master_connection`
--

DROP TABLE IF EXISTS `m004_master_connection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m004_master_connection` (
  `connection_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `db_server_type` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `db_hostname` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `db_username` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `db_password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `db_database` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` int DEFAULT NULL,
  `approved_on` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_approved` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`connection_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m005_master_http`
--

DROP TABLE IF EXISTS `m005_master_http`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m005_master_http` (
  `http_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` int DEFAULT NULL,
  `approved_on` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_approved` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`http_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m006_master_client`
--

DROP TABLE IF EXISTS `m006_master_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m006_master_client` (
  `client_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` int DEFAULT NULL,
  `approved_on` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_approved` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deleted` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `l001_link_allowed_route`
--

LOCK TABLES `l001_link_allowed_route` WRITE;
/*!40000 ALTER TABLE `l001_link_allowed_route` DISABLE KEYS */;
INSERT INTO `l001_link_allowed_route` VALUES 
(1,1,1,1,NULL,'2023-04-21 06:44:48',NULL,NULL,NULL,'2023-04-21 06:44:48','No','No','No'),
(2,1,2,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(3,1,3,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(4,1,4,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(5,1,5,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(6,1,6,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(7,1,7,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(8,1,8,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(9,1,9,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(10,1,10,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(11,1,11,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(12,1,12,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(13,1,13,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(14,1,14,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(15,1,1,2,NULL,'2023-04-21 06:44:48',NULL,NULL,NULL,'2023-04-21 06:44:48','No','No','No'),
(16,1,3,2,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(17,1,5,2,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(18,1,7,2,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(19,1,9,2,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(20,1,11,2,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(21,1,13,2,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(22,1,2,3,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(23,1,4,3,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(24,1,6,3,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(25,1,8,3,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(26,1,10,3,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(27,1,12,3,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(28,1,14,3,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(29,1,2,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(30,1,4,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(31,1,6,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(32,1,8,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(33,1,10,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(34,1,12,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(35,1,14,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(36,1,2,5,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(37,1,4,5,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(38,1,6,5,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(39,1,8,5,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(40,1,10,5,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(41,1,12,5,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(42,1,14,5,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(43,1,15,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(44,1,16,1,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(45,1,17,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(46,1,18,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(47,1,19,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(48,1,20,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(49,1,21,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(50,1,22,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(51,1,23,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(52,1,24,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(53,1,25,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(54,1,26,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(55,1,27,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(56,1,28,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(57,1,29,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(58,1,30,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(59,1,31,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(60,1,32,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(61,1,33,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(62,1,34,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(63,1,35,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(64,1,36,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No'),
(65,1,37,4,NULL,'2023-05-29 13:41:53',NULL,NULL,NULL,'2023-05-29 13:41:53','No','No','No');

/*!40000 ALTER TABLE `l001_link_allowed_route` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `m001_master_group`
--

LOCK TABLES `m001_master_group` WRITE;
/*!40000 ALTER TABLE `m001_master_group` DISABLE KEYS */;
INSERT INTO `m001_master_group` VALUES
(1,'Super Admins',1,1,NULL,'',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 06:38:22','No','No','No');
/*!40000 ALTER TABLE `m001_master_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `m002_master_user`
--

LOCK TABLES `m002_master_user` WRITE;
/*!40000 ALTER TABLE `m002_master_user` DISABLE KEYS */;
INSERT INTO `m002_master_user` VALUES
(1,'shames11@rediffmail.com','$2y$10$o8hFTjBIXQS.fOED2Ut1ZOCSdDjTnS3lyELI4rWyFEnu4GUyJr3O6',1,NULL,0,'2023-02-22 04:12:50',NULL,NULL,0,'2023-04-20 16:53:57','No','No','No');
/*!40000 ALTER TABLE `m002_master_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `m003_master_route`
--

LOCK TABLES `m003_master_route` WRITE;
/*!40000 ALTER TABLE `m003_master_route` DISABLE KEYS */;
INSERT INTO `m003_master_route` VALUES
(1,'/global/links',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(2,'/global/links/{link_id:int}',NULL,NULL,'2023-04-21 08:11:25',NULL,NULL,NULL,'2023-04-21 08:11:25','No','No','No'),
(3,'/global/groups',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(4,'/global/groups/{group_id:int}',NULL,NULL,'2023-04-21 08:11:25',NULL,NULL,NULL,'2023-04-21 08:11:25','No','No','No'),
(5,'/global/users',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(6,'/global/users/{user_id:int}',NULL,NULL,'2023-04-21 08:11:25',NULL,NULL,NULL,'2023-04-21 08:11:25','No','No','No'),
(7,'/global/routes',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(8,'/global/routes/{route_id:int}',NULL,NULL,'2023-04-21 08:11:25',NULL,NULL,NULL,'2023-04-21 08:11:25','No','No','No'),
(9,'/global/connections',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(10,'/global/connections/{connection_id:int}',NULL,NULL,'2023-04-21 08:11:25',NULL,NULL,NULL,'2023-04-21 08:11:25','No','No','No'),
(11,'/global/https',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(12,'/global/https/{http_id:int}',NULL,NULL,'2023-04-21 08:11:25',NULL,NULL,NULL,'2023-04-21 08:11:25','No','No','No'),
(13,'/global/clients',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(14,'/global/clients/{client_id:int}',NULL,NULL,'2023-04-21 08:11:25',NULL,NULL,NULL,'2023-04-21 08:11:25','No','No','No'),
(15,'/global/httproutes',NULL,NULL,'2023-05-29 13:41:05',NULL,NULL,NULL,'2023-05-29 13:41:05','No','No','No'),
(16,'/global/httproutes/{http:string|GET,POST,PUT,PATCH,DELETE}',NULL,NULL,'2023-05-29 13:41:05',NULL,NULL,NULL,'2023-05-29 13:41:05','No','No','No'),
(17,'/global/links/approve',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(18,'/global/groups/approve',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(19,'/global/users/approve',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(20,'/global/routes/approve',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(21,'/global/connections/approve',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(22,'/global/https/approve',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(23,'/global/clients/approve',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(24,'/global/links/enable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(25,'/global/groups/enable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(26,'/global/users/enable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(27,'/global/routes/enable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(28,'/global/connections/enable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(29,'/global/https/enable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(30,'/global/clients/enable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(31,'/global/links/disable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(32,'/global/groups/disable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(33,'/global/users/disable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(34,'/global/routes/disable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(35,'/global/connections/disable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(36,'/global/https/disable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(37,'/global/clients/disable',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 07:02:34','No','No','No'),
(38,'/upload/{module:string}',NULL,NULL,'2023-05-29 13:06:45',NULL,NULL,NULL,'2023-05-29 13:06:45','No','No','No'),
(39,'/thirdParty/{thirdParty:string}',NULL,NULL,'2023-05-29 13:41:05',NULL,NULL,NULL,'2023-05-29 13:41:05','No','No','No');
/*!40000 ALTER TABLE `m003_master_route` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `m004_master_connection`
--

LOCK TABLES `m004_master_connection` WRITE;
/*!40000 ALTER TABLE `m004_master_connection` DISABLE KEYS */;
INSERT INTO `m004_master_connection` VALUES
(1,'global','MySQL','dbHostnameDefault','dbUsernameDefault','dbPasswordDefault','globalDbName','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-05-02 14:48:16','No','No','No'),
(2,'clientOneConnectionName','MySQL','dbHostnameDefault','dbUsernameDefault','dbPasswordDefault','dbDatabaseClient001','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-05-02 14:50:41','No','No','No'),
(3,'clientTwoConnectionName','MySQL','dbHostnameClient002','dbUsernameClient002','dbPasswordClient002','dbDatabaseClient002','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-05-02 14:50:41','No','No','No');
/*!40000 ALTER TABLE `m004_master_connection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `m005_master_http`
--

LOCK TABLES `m005_master_http` WRITE;
/*!40000 ALTER TABLE `m005_master_http` DISABLE KEYS */;
INSERT INTO `m005_master_http` VALUES
(1,'GET','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 08:54:50','No','No','No'),
(2,'POST','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 08:54:50','No','No','No'),
(3,'PUT','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 08:54:50','No','No','No'),
(4,'PATCH','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 09:15:58','No','No','No'),
(5,'DELETE',NULL,NULL,'2023-04-15 09:16:22',NULL,NULL,NULL,'2023-04-15 09:16:22','No','No','No');
/*!40000 ALTER TABLE `m005_master_http` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `m006_master_client`
--

LOCK TABLES `m006_master_client` WRITE;
/*!40000 ALTER TABLE `m006_master_client` DISABLE KEYS */;
INSERT INTO `m006_master_client` VALUES
(1,'sahar.guru','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-29 16:00:41','No','No','Yes');
/*!40000 ALTER TABLE `m006_master_client` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-05-30 11:57:58
