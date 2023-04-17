CREATE DATABASE  IF NOT EXISTS `global` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `global`;
-- MySQL dump 10.13  Distrib 8.0.32, for macos13 (x86_64)
--
-- Host: localhost    Database: global
-- ------------------------------------------------------
-- Server version	8.0.32

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
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
  `id` int NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `l001_link_allowed_route`
--

LOCK TABLES `l001_link_allowed_route` WRITE;
/*!40000 ALTER TABLE `l001_link_allowed_route` DISABLE KEYS */;
/*!40000 ALTER TABLE `l001_link_allowed_route` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m001_master_group`
--

DROP TABLE IF EXISTS `m001_master_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m001_master_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `client_db_id` int NOT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m001_master_group`
--

LOCK TABLES `m001_master_group` WRITE;
/*!40000 ALTER TABLE `m001_master_group` DISABLE KEYS */;
INSERT INTO `m001_master_group` VALUES (1,'Super Admin',0,NULL,'',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 08:54:50','No','No','No');
/*!40000 ALTER TABLE `m001_master_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m002_master_user`
--

DROP TABLE IF EXISTS `m002_master_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m002_master_user` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m002_master_user`
--

LOCK TABLES `m002_master_user` WRITE;
/*!40000 ALTER TABLE `m002_master_user` DISABLE KEYS */;
INSERT INTO `m002_master_user` VALUES (1,'shames11@rediffmail.com','$2y$10$o8hFTjBIXQS.fOED2Ut1ZOCSdDjTnS3lyELI4rWyFEnu4GUyJr3O6',0,NULL,0,'2023-02-22 04:12:50',NULL,NULL,0,'2023-03-01 03:01:57','No','No','No');
/*!40000 ALTER TABLE `m002_master_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m003_master_route`
--

DROP TABLE IF EXISTS `m003_master_route`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m003_master_route` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m003_master_route`
--

LOCK TABLES `m003_master_route` WRITE;
/*!40000 ALTER TABLE `m003_master_route` DISABLE KEYS */;
INSERT INTO `m003_master_route` VALUES (1,'/m008_master_user',NULL,NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 08:54:50','No','No','No');
/*!40000 ALTER TABLE `m003_master_route` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m004_master_client_db`
--

DROP TABLE IF EXISTS `m004_master_client_db`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m004_master_client_db` (
  `id` int NOT NULL AUTO_INCREMENT,
  `db_hostname` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `db_username` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `db_password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `db_database` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `cache_hostname` varchar(255) NOT NULL,
  `cache_port` varchar(255) NOT NULL,
  `cache_password` varchar(255) NOT NULL,
  `cache_database` varchar(255) NOT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m004_master_client_db`
--

LOCK TABLES `m004_master_client_db` WRITE;
/*!40000 ALTER TABLE `m004_master_client_db` DISABLE KEYS */;
INSERT INTO `m004_master_client_db` VALUES (1,'localhost','','','product_global','','','','','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 08:54:50','No','No','No');
/*!40000 ALTER TABLE `m004_master_client_db` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m005_master_http`
--

DROP TABLE IF EXISTS `m005_master_http`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m005_master_http` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m005_master_http`
--

LOCK TABLES `m005_master_http` WRITE;
/*!40000 ALTER TABLE `m005_master_http` DISABLE KEYS */;
INSERT INTO `m005_master_http` VALUES (1,'GET','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 08:54:50','No','No','No'),(2,'POST','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 08:54:50','No','No','No'),(3,'PUT','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 08:54:50','No','No','No'),(4,'PATCH','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-15 09:15:58','No','No','No'),(5,'DELETE',NULL,NULL,'2023-04-15 09:16:22',NULL,NULL,NULL,'2023-04-15 09:16:22','No','No','No');
/*!40000 ALTER TABLE `m005_master_http` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-04-18  0:01:18
