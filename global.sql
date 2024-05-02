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
  `token` varchar(255) NOT NULL,
  `token_ts` int UNSIGNED DEFAULT 0,
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
-- Table structure for table `m003_master_connection`
--

DROP TABLE IF EXISTS `m003_master_connection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m003_master_connection` (
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
-- Table structure for table `m004_master_client`
--

DROP TABLE IF EXISTS `m004_master_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m004_master_client` (
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
-- Dumping data for table `m001_master_group`
--

LOCK TABLES `m001_master_group` WRITE;
/*!40000 ALTER TABLE `m001_master_group` DISABLE KEYS */;
INSERT INTO `m001_master_group` VALUES
(1,'AdminGroup',1,1,NULL,'',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 06:38:22','Yes','No','No'),
(2,'Client001UserGroup1',2,2,NULL,'',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 06:38:22','Yes','No','No'),
(3,'Client002UserGroup1',3,3,NULL,'',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-21 06:38:22','Yes','No','No');
/*!40000 ALTER TABLE `m001_master_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `m002_master_user`
--

LOCK TABLES `m002_master_user` WRITE;
/*!40000 ALTER TABLE `m002_master_user` DISABLE KEYS */;
INSERT INTO `m002_master_user` VALUES
(1,'shames11@rediffmail.com','$2y$10$o8hFTjBIXQS.fOED2Ut1ZOCSdDjTnS3lyELI4rWyFEnu4GUyJr3O6',1,'',0,NULL,0,'2023-02-22 04:12:50',NULL,NULL,0,'2023-04-20 16:53:57','Yes','No','No'),
(2,'client_1_user_1','$2y$10$o8hFTjBIXQS.fOED2Ut1ZOCSdDjTnS3lyELI4rWyFEnu4GUyJr3O6',2,'',0,NULL,0,'2023-02-22 04:12:50',NULL,NULL,0,'2023-04-20 16:53:57','Yes','No','No'),
(3,'client_2_user_1','$2y$10$o8hFTjBIXQS.fOED2Ut1ZOCSdDjTnS3lyELI4rWyFEnu4GUyJr3O6',3,'',0,NULL,0,'2023-02-22 04:12:50',NULL,NULL,0,'2023-04-20 16:53:57','Yes','No','No');
/*!40000 ALTER TABLE `m002_master_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `m003_master_connection`
--

LOCK TABLES `m003_master_connection` WRITE;
/*!40000 ALTER TABLE `m003_master_connection` DISABLE KEYS */;
INSERT INTO `m003_master_connection` VALUES
(1,'global','MySQL','dbHostnameDefault','dbUsernameDefault','dbPasswordDefault','globalDbName','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-05-02 14:48:16','Yes','No','No'),
(2,'clientOneConnectionName','MySQL','dbHostnameDefault','dbUsernameDefault','dbPasswordDefault','dbDatabaseClient001','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-05-02 14:50:41','Yes','No','No'),
(3,'clientTwoConnectionName','MySQL','dbHostnameClient002','dbUsernameClient002','dbPasswordClient002','dbDatabaseClient002','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-05-02 14:50:41','Yes','No','No');
/*!40000 ALTER TABLE `m003_master_connection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `m004_master_client`
--

LOCK TABLES `m004_master_client` WRITE;
/*!40000 ALTER TABLE `m004_master_client` DISABLE KEYS */;
INSERT INTO `m004_master_client` VALUES
(1,'Admins','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-29 16:00:41','Yes','No','No'),
(2,'Client 001','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-29 16:00:41','Yes','No','No'),
(3,'Client 002','',NULL,'2023-04-15 08:54:50',NULL,NULL,NULL,'2023-04-29 16:00:41','Yes','No','No');
/*!40000 ALTER TABLE `m004_master_client` ENABLE KEYS */;
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
