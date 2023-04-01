-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 01, 2023 at 09:37 AM
-- Server version: 8.0.28
-- PHP Version: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `global`
--

-- --------------------------------------------------------

--
-- Table structure for table `l001_link_allowed_route`
--

CREATE TABLE `l001_link_allowed_route` (
  `id` int NOT NULL,
  `group_id` int NOT NULL,
  `client_id` int NOT NULL,
  `module_id` int DEFAULT NULL,
  `route_id` int DEFAULT NULL,
  `http_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `m001_master_group`
--

CREATE TABLE `m001_master_group` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `allowed_ips` text,
  `client_ids` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `m001_master_group`
--

INSERT INTO `m001_master_group` (`id`, `name`, `comment`, `allowed_ips`, `client_ids`) VALUES
(1, 'Polygon Super Admin', '', NULL, 'null');

-- --------------------------------------------------------

--
-- Table structure for table `m002_master_client`
--

CREATE TABLE `m002_master_client` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `comment` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `m002_master_client`
--

INSERT INTO `m002_master_client` (`id`, `name`, `comment`) VALUES
(1, 'All', '');

-- --------------------------------------------------------

--
-- Table structure for table `m003_master_module`
--

CREATE TABLE `m003_master_module` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `comment` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `m003_master_module`
--

INSERT INTO `m003_master_module` (`id`, `name`, `comment`) VALUES
(1, 'All', '');

-- --------------------------------------------------------

--
-- Table structure for table `m004_master_route`
--

CREATE TABLE `m004_master_route` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `db_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `m004_master_route`
--

INSERT INTO `m004_master_route` (`id`, `name`, `route`, `db_id`) VALUES
(1, 'user', '/m008_master_user', 0);

-- --------------------------------------------------------

--
-- Table structure for table `m005_master_db`
--

CREATE TABLE `m005_master_db` (
  `id` int NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `database` varchar(255) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `m005_master_db`
--

INSERT INTO `m005_master_db` (`id`, `hostname`, `database`, `description`) VALUES
(1, 'localhost', 'product_global', '');

-- --------------------------------------------------------

--
-- Table structure for table `m006_master_http`
--

CREATE TABLE `m006_master_http` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `m006_master_http`
--

INSERT INTO `m006_master_http` (`id`, `name`, `description`) VALUES
(1, 'GET', ''),
(2, 'POST', ''),
(3, 'PUT', ''),
(4, 'DELETE', '');

-- --------------------------------------------------------

--
-- Table structure for table `m007_master_user`
--

CREATE TABLE `m007_master_user` (
  `id` int NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `group_ids` json DEFAULT NULL,
  `allowed_ips` text,
  `updated_by` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL DEFAULT '0',
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_disabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deleted` enum('Yes','No') NOT NULL DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `m007_master_user`
--

INSERT INTO `m007_master_user` (`id`, `username`, `password_hash`, `group_ids`, `allowed_ips`, `updated_by`, `created_by`, `updated_on`, `created_on`, `is_disabled`, `is_deleted`) VALUES
(1, 'shames11@rediffmail.com', '$2y$10$o8hFTjBIXQS.fOED2Ut1ZOCSdDjTnS3lyELI4rWyFEnu4GUyJr3O6', '1', '', 0, 0, '2023-03-01 03:01:57', '2023-02-22 04:12:50', 'No', 'No');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
