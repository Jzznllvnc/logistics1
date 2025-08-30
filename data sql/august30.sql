-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 30, 2025 at 03:34 PM
-- Server version: 8.0.42
-- PHP Version: 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `slate1`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `asset_type` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Operational',
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Assets table with image upload support for ALMS';

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_name`, `asset_type`, `purchase_date`, `status`, `image_path`, `created_at`) VALUES
(1, 'Forklift #1', 'Material Handling', '2023-05-15', 'Operational', 'assets/images/uploads/assets/asset_68ae9f2ecf211.png', '2025-08-21 17:42:03'),
(2, 'Delivery Truck - D07', 'Vehicle', '2022-11-20', 'Operational', 'assets/images/uploads/assets/asset_68ae9e3be22d5.png', '2025-08-21 17:42:03'),
(3, 'Pallet Jack - A', 'Material Handling', '2024-01-30', 'Under Maintenance', 'assets/images/uploads/assets/asset_68ae9fe352590.png', '2025-08-21 17:42:03'),
(4, 'Warehouse Conveyor Belt', 'Equipment', '2021-08-01', 'Operational', 'assets/images/uploads/assets/asset_68af1ca655f62.png', '2025-08-21 17:42:03'),
(5, 'Cargo Van - CV01', 'Vehicle', '2023-01-10', 'Operational', 'assets/images/uploads/assets/asset_68aea34ed961d.png', '2025-08-26 02:00:00'),
(6, 'Reach Truck - RT02', 'Material Handling', '2022-09-22', 'Operational', 'assets/images/uploads/assets/asset_68aea147d82be.png', '2025-08-26 02:00:00'),
(7, 'Shipping Container 40ft', 'Equipment', '2021-05-18', 'Operational', 'assets/images/uploads/assets/asset_68aea1c00556e.png', '2025-08-26 02:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `asset_forecast_cache`
--

CREATE TABLE `asset_forecast_cache` (
  `asset_id` int NOT NULL,
  `risk` varchar(50) DEFAULT NULL,
  `next_maintenance` varchar(100) DEFAULT NULL,
  `cached_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `asset_forecast_cache`
--

INSERT INTO `asset_forecast_cache` (`asset_id`, `risk`, `next_maintenance`, `cached_at`) VALUES
(1, 'Low', 'Feb 22, 2026', '2025-08-28 16:10:11'),
(2, 'Low', 'Nov 20, 2025', '2025-08-28 16:10:11'),
(3, 'Medium', 'Sep 27, 2025', '2025-08-28 16:10:11'),
(4, 'Low', 'May 1, 2026', '2025-08-28 16:10:11'),
(5, 'Low', 'Nov 27, 2025', '2025-08-28 16:10:11'),
(6, 'Low', 'May 10, 2026', '2025-08-28 16:10:11'),
(7, 'Low', 'Feb 20, 2026', '2025-08-28 16:10:11');

-- --------------------------------------------------------

--
-- Table structure for table `asset_usage_logs`
--

CREATE TABLE `asset_usage_logs` (
  `log_id` int NOT NULL,
  `asset_id` int NOT NULL,
  `log_date` date NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `asset_usage_logs`
--

INSERT INTO `asset_usage_logs` (`log_id`, `asset_id`, `log_date`, `metric_name`, `metric_value`) VALUES
(1, 1, '2025-05-01', 'Operating Hours', 1125.00),
(2, 1, '2025-06-01', 'Operating Hours', 1250.50),
(3, 1, '2025-07-01', 'Operating Hours', 1380.00),
(4, 1, '2025-08-01', 'Operating Hours', 1510.75),
(5, 1, '2025-08-25', 'Operating Hours', 1650.00),
(6, 2, '2025-05-01', 'Mileage', 52100.00),
(7, 2, '2025-06-01', 'Mileage', 55430.00),
(8, 2, '2025-07-01', 'Mileage', 58900.00),
(9, 2, '2025-08-01', 'Mileage', 62150.00),
(10, 2, '2025-08-25', 'Mileage', 65500.00),
(11, 3, '2025-05-01', 'Operating Hours', 850.00),
(12, 3, '2025-06-01', 'Operating Hours', 940.50),
(13, 3, '2025-07-01', 'Operating Hours', 1030.00),
(14, 3, '2025-08-01', 'Operating Hours', 1125.25),
(15, 4, '2025-05-01', 'Operating Hours', 8750.00),
(16, 4, '2025-06-01', 'Operating Hours', 9450.00),
(17, 4, '2025-07-01', 'Operating Hours', 10150.00),
(18, 4, '2025-08-01', 'Operating Hours', 10850.00),
(19, 5, '2025-05-01', 'Mileage', 18300.00),
(20, 5, '2025-06-01', 'Mileage', 22500.00),
(21, 5, '2025-07-01', 'Mileage', 26700.00),
(22, 5, '2025-08-01', 'Mileage', 31200.00),
(23, 5, '2025-08-25', 'Mileage', 34850.00),
(24, 6, '2025-05-01', 'Operating Hours', 2000.00),
(25, 6, '2025-06-01', 'Operating Hours', 2150.00),
(26, 6, '2025-07-01', 'Operating Hours', 2300.50),
(27, 6, '2025-08-01', 'Operating Hours', 2450.00),
(28, 6, '2025-08-25', 'Operating Hours', 2580.00),
(29, 7, '2025-05-01', 'Days In Use', 1443.00),
(30, 7, '2025-06-01', 'Days In Use', 1474.00),
(31, 7, '2025-07-01', 'Days In Use', 1504.00),
(32, 7, '2025-08-01', 'Days In Use', 1535.00),
(33, 4, '2025-05-01', 'Operating Hours', 8750.00),
(34, 4, '2025-06-01', 'Operating Hours', 9450.00),
(35, 4, '2025-07-01', 'Operating Hours', 10150.00),
(36, 4, '2025-08-01', 'Operating Hours', 10850.00),
(37, 7, '2025-05-01', 'Days In Use', 1443.00),
(38, 7, '2025-06-01', 'Days In Use', 1474.00),
(39, 7, '2025-07-01', 'Days In Use', 1504.00),
(40, 7, '2025-08-01', 'Days In Use', 1535.00);

-- --------------------------------------------------------

--
-- Table structure for table `bids`
--

CREATE TABLE `bids` (
  `id` int NOT NULL,
  `po_id` int NOT NULL,
  `supplier_id` int NOT NULL,
  `bid_amount` decimal(10,2) NOT NULL,
  `notes` text,
  `bid_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `bids`
--

INSERT INTO `bids` (`id`, `po_id`, `supplier_id`, `bid_amount`, `notes`, `bid_date`, `status`) VALUES
(7, 6, 10, 1233.00, 'penge pera', '2025-08-27 17:23:20', 'Pending'),
(11, 104, 11, 155.00, NULL, '2025-04-10 06:00:00', 'Awarded'),
(12, 104, 12, 160.00, NULL, '2025-04-10 06:05:00', 'Rejected'),
(13, 105, 12, 158.00, NULL, '2025-06-15 07:30:00', 'Awarded'),
(14, 105, 11, 162.00, NULL, '2025-06-15 07:35:00', 'Rejected'),
(15, 106, 11, 165.00, NULL, '2025-08-05 02:00:00', 'Awarded'),
(16, 106, 13, 168.00, NULL, '2025-08-05 02:05:00', 'Rejected'),
(17, 107, 13, 55.00, NULL, '2025-05-20 01:00:00', 'Awarded'),
(18, 107, 14, 58.00, NULL, '2025-05-20 01:05:00', 'Rejected'),
(19, 108, 14, 54.00, NULL, '2025-07-18 03:45:00', 'Awarded'),
(20, 108, 13, 57.00, NULL, '2025-07-18 03:50:00', 'Rejected'),
(21, 109, 13, 60.00, NULL, '2025-08-22 08:00:00', 'Awarded'),
(22, 109, 15, 62.00, NULL, '2025-08-22 08:05:00', 'Rejected'),
(23, 110, 15, 25.00, NULL, '2025-05-01 00:00:00', 'Awarded'),
(24, 110, 16, 26.50, NULL, '2025-05-01 00:05:00', 'Rejected'),
(25, 111, 16, 25.50, NULL, '2025-07-10 05:00:00', 'Awarded'),
(26, 111, 15, 27.00, NULL, '2025-07-10 05:05:00', 'Rejected');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `file_name`, `file_path`, `document_type`, `reference_number`, `expiry_date`, `upload_date`) VALUES
(1, 'sample-bill-of-lading.pdf', 'uploads/sample-bill-of-lading.pdf', 'Bill of Lading', 'BOL-789XYZ', '2026-12-31', '2025-08-21 17:48:11');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `quantity`, `last_updated`) VALUES
(4, 'tape', 50, '2025-08-30 15:28:48'),
(5, 'Standard Pallets (48x40)', 150, '2025-08-21 16:15:36'),
(6, 'Euro Pallets (1200x800)', 80, '2025-08-21 16:15:36'),
(7, 'Heat Treated Pallets (ISPM-15)', 65, '2025-08-21 16:15:36'),
(8, 'Shrink Wrap Rolls', 320, '2025-08-25 08:42:25'),
(9, 'Packing Tape Rolls', 400, '2025-08-21 16:15:36'),
(10, 'Cardboard Boxes (Large)', 800, '2025-08-21 16:15:36'),
(11, 'Cardboard Boxes (Medium)', 1200, '2025-08-21 16:15:36'),
(12, 'Cardboard Boxes (Small)', 1490, '2025-08-30 15:28:56'),
(13, 'Bubble Wrap Rolls', 75, '2025-08-21 16:15:36'),
(14, 'Shipping Labels (Pack of 1000)', 50, '2025-08-21 16:15:36'),
(15, 'Bill of Lading Forms (Pack of 500)', 59, '2025-08-26 20:09:22'),
(17, 'Cargo Straps', 110, '2025-08-26 15:55:37'),
(18, 'Safety Box Cutters', 30, '2025-08-21 16:15:36'),
(19, 'Work Gloves (Pairs)', 90, '2025-08-21 16:15:36');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_forecast_cache`
--

CREATE TABLE `inventory_forecast_cache` (
  `item_id` int NOT NULL,
  `analysis` text,
  `action` varchar(255) DEFAULT NULL,
  `cached_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventory_forecast_cache`
--

INSERT INTO `inventory_forecast_cache` (`item_id`, `analysis`, `action`, `cached_at`) VALUES
(4, 'API Error', 'Error', '2025-08-30 15:28:39'),
(5, 'API Error', 'Error', '2025-08-30 15:28:39'),
(6, 'API Error', 'Error', '2025-08-30 15:28:31'),
(7, 'API Error', 'Error', '2025-08-30 15:28:31'),
(8, 'API Error', 'Error', '2025-08-30 15:28:39'),
(9, 'API Error', 'Error', '2025-08-30 15:28:31'),
(10, 'API Error', 'Error', '2025-08-30 15:28:31'),
(11, 'API Error', 'Error', '2025-08-30 15:28:31'),
(12, 'API Error', 'Error', '2025-08-30 15:28:31'),
(13, 'API Error', 'Error', '2025-08-30 15:28:31'),
(14, 'API Error', 'Error', '2025-08-30 15:28:39'),
(15, 'API Error', 'Error', '2025-08-30 15:28:31'),
(17, 'API Error', 'Error', '2025-08-30 15:28:31'),
(18, 'API Error', 'Error', '2025-08-30 15:28:31'),
(19, 'API Error', 'Error', '2025-08-30 15:28:39');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_history`
--

CREATE TABLE `inventory_history` (
  `id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventory_history`
--

INSERT INTO `inventory_history` (`id`, `item_id`, `quantity`, `timestamp`) VALUES
(1, 4, 100, '2025-05-27 09:06:00'),
(2, 4, 95, '2025-06-01 09:06:00'),
(3, 4, 90, '2025-06-06 09:06:00'),
(4, 4, 85, '2025-06-11 09:06:00'),
(5, 4, 78, '2025-06-16 09:06:00'),
(6, 4, 72, '2025-06-21 09:06:00'),
(7, 4, 65, '2025-06-26 09:06:00'),
(8, 4, 58, '2025-07-01 09:06:00'),
(9, 4, 51, '2025-07-06 09:06:00'),
(10, 4, 45, '2025-07-11 09:06:00'),
(11, 4, 40, '2025-07-16 09:06:00'),
(12, 4, 35, '2025-07-21 09:06:00'),
(13, 4, 30, '2025-07-26 09:06:00'),
(14, 4, 28, '2025-08-05 09:06:00'),
(15, 4, 25, '2025-08-15 09:06:00'),
(16, 4, 23, '2025-08-24 09:06:00'),
(17, 5, 250, '2025-05-27 09:06:00'),
(18, 5, 245, '2025-05-29 09:06:00'),
(19, 5, 240, '2025-06-01 09:06:00'),
(20, 5, 235, '2025-06-04 09:06:00'),
(21, 5, 228, '2025-06-07 09:06:00'),
(22, 5, 221, '2025-06-10 09:06:00'),
(23, 5, 215, '2025-06-13 09:06:00'),
(24, 5, 209, '2025-06-16 09:06:00'),
(25, 5, 202, '2025-06-19 09:06:00'),
(26, 5, 195, '2025-06-22 09:06:00'),
(27, 5, 188, '2025-06-25 09:06:00'),
(28, 5, 180, '2025-06-28 09:06:00'),
(29, 5, 173, '2025-07-01 09:06:00'),
(30, 5, 350, '2025-07-04 09:06:00'),
(31, 5, 340, '2025-07-07 09:06:00'),
(32, 5, 332, '2025-07-10 09:06:00'),
(33, 5, 325, '2025-07-13 09:06:00'),
(34, 5, 317, '2025-07-16 09:06:00'),
(35, 5, 309, '2025-07-19 09:06:00'),
(36, 5, 300, '2025-07-22 09:06:00'),
(37, 5, 291, '2025-07-25 09:06:00'),
(38, 5, 282, '2025-07-28 09:06:00'),
(39, 5, 273, '2025-07-31 09:06:00'),
(40, 5, 264, '2025-08-03 09:06:00'),
(41, 5, 255, '2025-08-06 09:06:00'),
(42, 5, 245, '2025-08-09 09:06:00'),
(43, 5, 235, '2025-08-12 09:06:00'),
(44, 5, 220, '2025-08-15 09:06:00'),
(45, 5, 205, '2025-08-18 09:06:00'),
(46, 5, 185, '2025-08-21 09:06:00'),
(47, 5, 165, '2025-08-23 09:06:00'),
(48, 5, 150, '2025-08-24 09:06:00'),
(49, 6, 120, '2025-05-27 09:06:00'),
(50, 6, 118, '2025-06-01 09:06:00'),
(51, 6, 115, '2025-06-06 09:06:00'),
(52, 6, 112, '2025-06-11 09:06:00'),
(53, 6, 110, '2025-06-16 09:06:00'),
(54, 6, 108, '2025-06-21 09:06:00'),
(55, 6, 105, '2025-06-26 09:06:00'),
(56, 6, 103, '2025-07-01 09:06:00'),
(57, 6, 100, '2025-07-06 09:06:00'),
(58, 6, 98, '2025-07-11 09:06:00'),
(59, 6, 95, '2025-07-16 09:06:00'),
(60, 6, 92, '2025-07-21 09:06:00'),
(61, 6, 90, '2025-07-26 09:06:00'),
(62, 6, 88, '2025-07-31 09:06:00'),
(63, 6, 85, '2025-08-05 09:06:00'),
(64, 6, 82, '2025-08-10 09:06:00'),
(65, 6, 80, '2025-08-15 09:06:00'),
(66, 7, 100, '2025-05-27 09:06:00'),
(67, 7, 100, '2025-06-06 09:06:00'),
(68, 7, 80, '2025-06-16 09:06:00'),
(69, 7, 80, '2025-06-26 09:06:00'),
(70, 7, 75, '2025-07-06 09:06:00'),
(71, 7, 75, '2025-07-16 09:06:00'),
(72, 7, 65, '2025-07-26 09:06:00'),
(73, 7, 65, '2025-08-05 09:06:00'),
(74, 7, 65, '2025-08-15 09:06:00'),
(75, 8, 400, '2025-05-27 09:06:00'),
(76, 8, 380, '2025-05-29 09:06:00'),
(77, 8, 365, '2025-05-31 09:06:00'),
(78, 8, 350, '2025-06-02 09:06:00'),
(79, 8, 330, '2025-06-04 09:06:00'),
(80, 8, 310, '2025-06-06 09:06:00'),
(81, 8, 350, '2025-06-08 09:06:00'),
(82, 8, 335, '2025-06-10 09:06:00'),
(83, 8, 315, '2025-06-12 09:06:00'),
(84, 8, 290, '2025-06-14 09:06:00'),
(85, 8, 270, '2025-06-16 09:06:00'),
(86, 8, 320, '2025-06-18 09:06:00'),
(87, 8, 300, '2025-06-20 09:06:00'),
(88, 8, 285, '2025-06-22 09:06:00'),
(89, 8, 270, '2025-06-24 09:06:00'),
(90, 8, 250, '2025-06-26 09:06:00'),
(91, 8, 230, '2025-06-28 09:06:00'),
(92, 8, 280, '2025-06-30 09:06:00'),
(93, 8, 265, '2025-07-02 09:06:00'),
(94, 8, 250, '2025-07-04 09:06:00'),
(95, 8, 235, '2025-07-06 09:06:00'),
(96, 8, 350, '2025-07-08 09:06:00'),
(97, 8, 330, '2025-07-11 09:06:00'),
(98, 8, 310, '2025-07-14 09:06:00'),
(99, 8, 295, '2025-07-17 09:06:00'),
(100, 8, 280, '2025-07-20 09:06:00'),
(101, 8, 340, '2025-07-23 09:06:00'),
(102, 8, 325, '2025-07-26 09:06:00'),
(103, 8, 310, '2025-07-31 09:06:00'),
(104, 8, 290, '2025-08-05 09:06:00'),
(105, 8, 270, '2025-08-10 09:06:00'),
(106, 8, 330, '2025-08-15 09:06:00'),
(107, 8, 320, '2025-08-20 09:06:00'),
(108, 9, 200, '2025-05-27 09:06:00'),
(109, 9, 180, '2025-06-06 09:06:00'),
(110, 9, 160, '2025-06-16 09:06:00'),
(111, 9, 140, '2025-06-26 09:06:00'),
(112, 9, 120, '2025-07-06 09:06:00'),
(113, 9, 100, '2025-07-16 09:06:00'),
(114, 9, 500, '2025-07-26 09:06:00'),
(115, 9, 480, '2025-07-31 09:06:00'),
(116, 9, 460, '2025-08-05 09:06:00'),
(117, 9, 440, '2025-08-10 09:06:00'),
(118, 9, 420, '2025-08-15 09:06:00'),
(119, 9, 400, '2025-08-20 09:06:00'),
(120, 10, 1500, '2025-05-27 09:06:00'),
(121, 10, 1480, '2025-05-30 09:06:00'),
(122, 10, 1460, '2025-06-02 09:06:00'),
(123, 10, 1435, '2025-06-05 09:06:00'),
(124, 10, 1410, '2025-06-08 09:06:00'),
(125, 10, 1390, '2025-06-11 09:06:00'),
(126, 10, 1365, '2025-06-14 09:06:00'),
(127, 10, 1340, '2025-06-17 09:06:00'),
(128, 10, 1315, '2025-06-20 09:06:00'),
(129, 10, 1290, '2025-06-23 09:06:00'),
(130, 10, 1260, '2025-06-26 09:06:00'),
(131, 10, 1230, '2025-06-29 09:06:00'),
(132, 10, 1200, '2025-07-02 09:06:00'),
(133, 10, 1175, '2025-07-05 09:06:00'),
(134, 10, 1150, '2025-07-08 09:06:00'),
(135, 10, 1120, '2025-07-11 09:06:00'),
(136, 10, 1095, '2025-07-14 09:06:00'),
(137, 10, 1070, '2025-07-17 09:06:00'),
(138, 10, 1040, '2025-07-20 09:06:00'),
(139, 10, 1010, '2025-07-23 09:06:00'),
(140, 10, 985, '2025-07-26 09:06:00'),
(141, 10, 960, '2025-07-29 09:06:00'),
(142, 10, 930, '2025-08-01 09:06:00'),
(143, 10, 905, '2025-08-04 09:06:00'),
(144, 10, 880, '2025-08-07 09:06:00'),
(145, 10, 860, '2025-08-10 09:06:00'),
(146, 10, 845, '2025-08-13 09:06:00'),
(147, 10, 825, '2025-08-16 09:06:00'),
(148, 10, 810, '2025-08-19 09:06:00'),
(149, 10, 800, '2025-08-22 09:06:00'),
(150, 11, 1300, '2025-05-27 09:06:00'),
(151, 11, 1290, '2025-06-01 09:06:00'),
(152, 11, 1285, '2025-06-06 09:06:00'),
(153, 11, 1275, '2025-06-11 09:06:00'),
(154, 11, 1270, '2025-06-16 09:06:00'),
(155, 11, 1500, '2025-06-21 09:06:00'),
(156, 11, 1480, '2025-06-26 09:06:00'),
(157, 11, 1470, '2025-07-01 09:06:00'),
(158, 11, 1460, '2025-07-06 09:06:00'),
(159, 11, 1450, '2025-07-11 09:06:00'),
(160, 11, 1440, '2025-07-16 09:06:00'),
(161, 11, 1420, '2025-07-21 09:06:00'),
(162, 11, 1400, '2025-07-26 09:06:00'),
(163, 11, 1380, '2025-07-31 09:06:00'),
(164, 11, 1350, '2025-08-05 09:06:00'),
(165, 11, 1320, '2025-08-10 09:06:00'),
(166, 11, 1280, '2025-08-15 09:06:00'),
(167, 11, 1240, '2025-08-20 09:06:00'),
(168, 11, 1200, '2025-08-24 09:06:00'),
(169, 12, 1550, '2025-05-27 09:06:00'),
(170, 12, 1545, '2025-06-06 09:06:00'),
(171, 12, 1540, '2025-06-16 09:06:00'),
(172, 12, 1530, '2025-06-26 09:06:00'),
(173, 12, 1525, '2025-07-06 09:06:00'),
(174, 12, 1520, '2025-07-16 09:06:00'),
(175, 12, 1510, '2025-07-26 09:06:00'),
(176, 12, 1505, '2025-08-05 09:06:00'),
(177, 12, 1500, '2025-08-15 09:06:00'),
(178, 13, 150, '2025-05-27 09:06:00'),
(179, 13, 148, '2025-06-06 09:06:00'),
(180, 13, 145, '2025-06-16 09:06:00'),
(181, 13, 142, '2025-06-26 09:06:00'),
(182, 13, 140, '2025-07-06 09:06:00'),
(183, 13, 135, '2025-07-16 09:06:00'),
(184, 13, 120, '2025-07-26 09:06:00'),
(185, 13, 100, '2025-08-05 09:06:00'),
(186, 13, 85, '2025-08-15 09:06:00'),
(187, 13, 75, '2025-08-24 09:06:00'),
(188, 14, 70, '2025-05-27 09:06:00'),
(189, 14, 68, '2025-06-06 09:06:00'),
(190, 14, 66, '2025-06-16 09:06:00'),
(191, 14, 64, '2025-06-26 09:06:00'),
(192, 14, 62, '2025-07-06 09:06:00'),
(193, 14, 60, '2025-07-16 09:06:00'),
(194, 14, 58, '2025-07-26 09:06:00'),
(195, 14, 55, '2025-08-05 09:06:00'),
(196, 14, 52, '2025-08-15 09:06:00'),
(197, 14, 50, '2025-08-24 09:06:00'),
(198, 15, 50, '2025-05-27 09:06:00'),
(199, 15, 48, '2025-06-06 09:06:00'),
(200, 15, 45, '2025-06-16 09:06:00'),
(201, 15, 42, '2025-06-26 09:06:00'),
(202, 15, 40, '2025-07-06 09:06:00'),
(203, 15, 38, '2025-07-16 09:06:00'),
(204, 15, 35, '2025-07-26 09:06:00'),
(205, 15, 30, '2025-08-05 09:06:00'),
(206, 15, 27, '2025-08-15 09:06:00'),
(207, 15, 25, '2025-08-24 09:06:00'),
(217, 17, 80, '2025-05-27 09:06:00'),
(218, 17, 85, '2025-06-06 09:06:00'),
(219, 17, 90, '2025-06-16 09:06:00'),
(220, 17, 95, '2025-06-26 09:06:00'),
(221, 17, 100, '2025-07-06 09:06:00'),
(222, 17, 105, '2025-07-16 09:06:00'),
(223, 17, 110, '2025-07-26 09:06:00'),
(224, 17, 115, '2025-08-05 09:06:00'),
(225, 17, 120, '2025-08-15 09:06:00'),
(226, 18, 35, '2025-05-27 09:06:00'),
(227, 18, 34, '2025-06-06 09:06:00'),
(228, 18, 34, '2025-06-16 09:06:00'),
(229, 18, 33, '2025-06-26 09:06:00'),
(230, 18, 32, '2025-07-06 09:06:00'),
(231, 18, 32, '2025-07-16 09:06:00'),
(232, 18, 31, '2025-07-26 09:06:00'),
(233, 18, 30, '2025-08-05 09:06:00'),
(234, 18, 30, '2025-08-15 09:06:00'),
(235, 19, 100, '2025-05-27 09:06:00'),
(236, 19, 95, '2025-06-01 09:06:00'),
(237, 19, 150, '2025-06-06 09:06:00'),
(238, 19, 140, '2025-06-11 09:06:00'),
(239, 19, 130, '2025-06-16 09:06:00'),
(240, 19, 100, '2025-06-21 09:06:00'),
(241, 19, 98, '2025-06-26 09:06:00'),
(242, 19, 95, '2025-07-06 09:06:00'),
(243, 19, 92, '2025-07-16 09:06:00'),
(244, 19, 90, '2025-07-26 09:06:00'),
(359, 4, 50, '2025-08-30 15:28:48'),
(360, 12, 1490, '2025-08-30 15:28:56');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_history`
--

CREATE TABLE `maintenance_history` (
  `id` int NOT NULL,
  `asset_id` int NOT NULL,
  `status` varchar(50) NOT NULL,
  `notes` text,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `maintenance_history`
--

INSERT INTO `maintenance_history` (`id`, `asset_id`, `status`, `notes`, `timestamp`) VALUES
(3, 1, 'Operational', 'Maintenance complete.', '2024-11-22 06:00:00'),
(4, 2, 'Operational', 'Initial registration.', '2022-11-20 03:00:00'),
(5, 2, 'Under Maintenance', 'Engine oil change.', '2025-08-20 00:30:00'),
(6, 2, 'Operational', 'Service complete.', '2025-08-21 08:00:00'),
(7, 3, 'Operational', 'Initial registration.', '2024-01-30 04:00:00'),
(8, 3, 'Under Maintenance', 'Hydraulic fluid leak.', '2025-08-21 09:42:03'),
(9, 4, 'Decommissioned', 'Status updated.', '2025-08-26 15:12:02'),
(10, 5, 'Operational', 'Initial registration.', '2023-01-10 01:00:00'),
(11, 5, 'Under Maintenance', 'Oil change and tire rotation.', '2024-07-15 03:00:00'),
(12, 5, 'Operational', 'Service complete.', '2024-07-16 08:30:00'),
(13, 6, 'Operational', 'Initial registration.', '2022-09-22 01:00:00'),
(14, 6, 'Under Maintenance', 'Battery replacement.', '2025-03-10 06:00:00'),
(15, 6, 'Operational', 'Maintenance complete.', '2025-03-10 09:00:00'),
(16, 7, 'Operational', 'Initial registration.', '2021-05-18 01:00:00'),
(17, 3, 'Operational', 'Maintenance task completed.', '2025-08-26 15:38:53'),
(18, 3, 'Operational', 'Maintenance task completed.', '2025-08-26 15:39:01'),
(19, 2, 'Operational', 'Maintenance task completed.', '2025-08-26 15:39:53'),
(20, 3, 'Operational', 'Maintenance task completed.', '2025-08-26 15:41:28'),
(21, 4, 'Operational', 'Status updated.', '2025-08-26 15:42:03'),
(22, 4, 'Operational', 'Initial registration.', '2021-08-01 02:00:00'),
(23, 4, 'Under Maintenance', 'Replaced motor brushes.', '2024-12-10 01:00:00'),
(24, 4, 'Operational', 'Maintenance complete.', '2024-12-11 07:00:00'),
(25, 7, 'Operational', 'Initial registration.', '2021-05-18 03:00:00'),
(26, 7, 'Under Maintenance', 'Structural integrity check and door seal replacement.', '2025-01-20 05:00:00'),
(27, 7, 'Operational', 'Inspection passed, maintenance complete.', '2025-01-22 02:00:00'),
(28, 2, 'Operational', 'Maintenance task completed.', '2025-08-26 15:49:09'),
(30, 5, 'Operational', 'Status updated.', '2025-08-26 19:12:20'),
(31, 5, 'Operational', 'Status updated.', '2025-08-26 19:12:25'),
(32, 5, 'Operational', 'Status updated.', '2025-08-26 19:17:12'),
(33, 5, 'Operational', 'Status updated.', '2025-08-26 19:17:23'),
(34, 5, 'Operational', 'Status updated.', '2025-08-26 19:19:51'),
(35, 5, 'Operational', 'Status updated.', '2025-08-26 19:19:58'),
(37, 5, 'Operational', 'Status updated.', '2025-08-26 19:24:53'),
(38, 5, 'Operational', 'Status updated.', '2025-08-26 19:25:00'),
(39, 5, 'Operational', 'Status updated.', '2025-08-27 05:44:56'),
(40, 2, 'Operational', 'Status updated.', '2025-08-27 05:57:15'),
(41, 1, 'Operational', 'Status updated.', '2025-08-27 06:01:18'),
(42, 3, 'Under Maintenance', 'Status updated.', '2025-08-27 06:04:19'),
(43, 6, 'Operational', 'Status updated.', '2025-08-27 06:07:33'),
(44, 6, 'Operational', 'Status updated.', '2025-08-27 06:10:15'),
(45, 7, 'Operational', 'Status updated.', '2025-08-27 06:12:16'),
(46, 4, 'Operational', 'Status updated.', '2025-08-27 06:15:37'),
(47, 5, 'Operational', 'Status updated.', '2025-08-27 06:18:54');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_schedules`
--

CREATE TABLE `maintenance_schedules` (
  `id` int NOT NULL,
  `asset_id` int NOT NULL,
  `task_description` text NOT NULL,
  `scheduled_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Scheduled',
  `completed_date` date DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `maintenance_schedules`
--

INSERT INTO `maintenance_schedules` (`id`, `asset_id`, `task_description`, `scheduled_date`, `status`, `completed_date`, `notes`) VALUES
(3, 3, 'AI Recommended: Proactive check-up.', '2026-02-21', 'Scheduled', NULL, 'Automated based on Medium risk prediction.'),
(4, 5, 'AI Recommended: Proactive check-up.', '2025-11-15', 'Scheduled', NULL, 'Automated based on Medium risk prediction.'),
(10, 3, 'AI Recommended: Proactive check-up.', '2025-09-21', 'Completed', '2025-08-26', 'Automated based on High risk prediction.'),
(11, 2, 'AI Recommended: Proactive check-up.', '2025-10-20', 'Completed', '2025-08-26', 'Automated based on High risk prediction.'),
(12, 6, 'AI Recommended: Proactive check-up.', '2026-05-10', 'Scheduled', NULL, 'Automated based on Medium risk prediction.');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `supplier_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `supplier_id`, `message`, `is_read`, `created_at`) VALUES
(4, 10, 'Congratulations! Your bid for \'tape\' (PO #1) has been awarded.', 1, '2025-08-27 17:11:48');

-- --------------------------------------------------------

--
-- Table structure for table `price_forecast_cache`
--

CREATE TABLE `price_forecast_cache` (
  `item_name` varchar(255) NOT NULL,
  `forecast_text` text,
  `cached_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `price_forecast_cache`
--

INSERT INTO `price_forecast_cache` (`item_name`, `forecast_text`, `cached_at`) VALUES
('Cardboard Boxes (Large)', 'API Error', '2025-08-30 15:32:08'),
('Packing Tape Rolls', 'API Error', '2025-08-30 15:32:08'),
('Shrink Wrap Rolls', 'API Error', '2025-08-30 15:28:39');

-- --------------------------------------------------------

--
-- Table structure for table `procurement_analysis_cache`
--

CREATE TABLE `procurement_analysis_cache` (
  `po_id` int NOT NULL,
  `analysis_text` text,
  `cached_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `procurement_analysis_cache`
--

INSERT INTO `procurement_analysis_cache` (`po_id`, `analysis_text`, `cached_at`) VALUES
(6, 'Supplier1 submitted only one bid and was unsuccessful, resulting in a 0% win rate and a single bid of ₱1,233.00.  Recommendation: Supplier1 should be excluded from future bidding opportunities due to their poor performance and lack of demonstrated competitiveness.\n', '2025-08-29 14:52:34'),
(104, 'Supplier 2 has a strong win rate of 67% across three bids and offers a competitive current bid of ₱155.00.  Supplier 3 has a lower win rate of 50% from only two bids and offers a slightly higher current bid of ₱160.00.\n\nRecommendation: Supplier 2 is recommended due to their higher win rate and lower current bid.\n', '2025-08-29 15:10:30'),
(110, 'Supplier 6 has a low win rate despite submitting three bids, with their current bid being the lowest at ₱25.00.  Supplier 7, while having a higher win rate (50%), submitted fewer bids and has a higher current bid of ₱26.50. Recommendation:  Select Supplier 6 due to their lower current bid, despite the lower win rate, as the price difference outweighs the risk.\n', '2025-08-29 15:10:51');

-- --------------------------------------------------------

--
-- Table structure for table `procurement_requests`
--

CREATE TABLE `procurement_requests` (
  `id` int NOT NULL,
  `item_id` int NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `request_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `requester_username` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `description` text,
  `status` varchar(50) DEFAULT 'Not Started',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_name`, `description`, `status`, `start_date`, `end_date`, `created_at`) VALUES
(1, 'Cross-Country Warehouse Transfer', 'Coordinate the full transfer of inventory from the West Coast warehouse to the new East Coast distribution center.', 'In Progress', '2025-09-01', '2025-09-30', '2025-08-21 16:45:02'),
(2, 'rovic', 'rovic', 'Completed', '2025-08-04', '2025-08-30', '2025-08-21 16:49:52');

-- --------------------------------------------------------

--
-- Table structure for table `project_resources`
--

CREATE TABLE `project_resources` (
  `project_id` int NOT NULL,
  `supplier_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int NOT NULL,
  `supplier_id` int DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `awarded_to_supplier_id` int DEFAULT NULL,
  `awarded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `supplier_id`, `item_name`, `quantity`, `status`, `order_date`, `awarded_to_supplier_id`, `awarded_at`) VALUES
(5, NULL, 'Standard Pallets (48x40)', 31, 'Open for Bidding', '2025-08-27 17:18:07', NULL, NULL),
(6, NULL, 'Work Gloves (Pairs)', 123123, 'Open for Bidding', '2025-08-27 17:20:35', NULL, NULL),
(104, NULL, 'Shrink Wrap Rolls', 200, 'Awarded', '2025-04-10 06:00:00', NULL, NULL),
(105, NULL, 'Shrink Wrap Rolls', 250, 'Awarded', '2025-06-15 07:30:00', NULL, NULL),
(106, NULL, 'Shrink Wrap Rolls', 220, 'Awarded', '2025-08-05 02:00:00', NULL, NULL),
(107, NULL, 'Packing Tape Rolls', 500, 'Awarded', '2025-05-20 01:00:00', NULL, NULL),
(108, NULL, 'Packing Tape Rolls', 450, 'Awarded', '2025-07-18 03:45:00', NULL, NULL),
(109, NULL, 'Packing Tape Rolls', 550, 'Awarded', '2025-08-22 08:00:00', NULL, NULL),
(110, NULL, 'Cardboard Boxes (Large)', 1000, 'Awarded', '2025-05-01 00:00:00', NULL, NULL),
(111, NULL, 'Cardboard Boxes (Large)', 1200, 'Awarded', '2025-07-10 05:00:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text,
  `verification_document_path` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `email`, `phone`, `address`, `verification_document_path`, `status`, `created_at`) VALUES
(10, 'supplier1', 'sup1', 'jazznelle002@gmail.com', '099123', 'weqweqwe', 'uploads/verification/68af3bde25e40-cor (1) (1).pdf', 'Approved', '2025-08-27 17:09:50'),
(11, 'Supplier 2', 'Ana Reyes', 'ana.reyes@supplier2.com', '09171234567', '123 Ayala Ave, Makati City', NULL, 'Approved', '2025-08-29 15:09:42'),
(12, 'Supplier 3', 'Juan dela Cruz', 'juan.dc@supplier3.com', '09209876543', '456 Magsaysay St, Cebu City', NULL, 'Approved', '2025-08-29 15:09:42'),
(13, 'Supplier 4', 'Maria Santos', 'maria.santos@supplier4.com', '09181112233', '789 Rizal Ave, Davao City', NULL, 'Approved', '2025-08-29 15:09:42'),
(14, 'Supplier 5', 'Carlos Mendoza', 'carlos.m@supplier5.net', '09157654321', '101 Bonifacio St, Baguio City', NULL, 'Approved', '2025-08-29 15:09:42'),
(15, 'Supplier 6', 'Lita Garcia', 'lita.garcia@supplier6.com', '09284455667', '213 Roxas Blvd, Pasay City', NULL, 'Approved', '2025-08-29 15:09:42'),
(16, 'Supplier 7', 'Ramon Lim', 'ramon.lim@supplier7.com', '09338889900', '314 Quezon Ave, Quezon City', NULL, 'Approved', '2025-08-29 15:09:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `supplier_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `supplier_id`, `created_at`) VALUES
(1, 'admin', 'admin123', 'admin', NULL, '2025-08-21 17:20:50'),
(2, 'warehouse', 'wh123', 'smart_warehousing', NULL, '2025-08-21 17:20:50'),
(3, 'procure', 'pr123', 'procurement', NULL, '2025-08-21 17:20:50'),
(4, 'pltuser', 'plt123', 'plt', NULL, '2025-08-21 17:20:50'),
(5, 'almsuser', 'alms123', 'alms', NULL, '2025-08-21 17:20:50'),
(6, 'dtrsuser', 'dtrs123', 'dtrs', NULL, '2025-08-21 17:20:50'),
(12, 'sup1', 'sup123', 'supplier', 10, '2025-08-27 17:09:50'),
(13, 'sup2', 'pass123', 'supplier', 11, '2025-08-29 15:09:42'),
(14, 'sup3', 'pass123', 'supplier', 12, '2025-08-29 15:09:42'),
(15, 'sup4', 'pass123', 'supplier', 13, '2025-08-29 15:09:42'),
(16, 'sup5', 'pass123', 'supplier', 14, '2025-08-29 15:09:42'),
(17, 'sup6', 'pass123', 'supplier', 15, '2025-08-29 15:09:42'),
(18, 'sup7', 'pass123', 'supplier', 16, '2025-08-29 15:09:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `asset_forecast_cache`
--
ALTER TABLE `asset_forecast_cache`
  ADD PRIMARY KEY (`asset_id`);

--
-- Indexes for table `asset_usage_logs`
--
ALTER TABLE `asset_usage_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `bids`
--
ALTER TABLE `bids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_name` (`item_name`);

--
-- Indexes for table `inventory_forecast_cache`
--
ALTER TABLE `inventory_forecast_cache`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `maintenance_history`
--
ALTER TABLE `maintenance_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `maintenance_schedules`
--
ALTER TABLE `maintenance_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `price_forecast_cache`
--
ALTER TABLE `price_forecast_cache`
  ADD PRIMARY KEY (`item_name`);

--
-- Indexes for table `procurement_analysis_cache`
--
ALTER TABLE `procurement_analysis_cache`
  ADD PRIMARY KEY (`po_id`);

--
-- Indexes for table `procurement_requests`
--
ALTER TABLE `procurement_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_resources`
--
ALTER TABLE `project_resources`
  ADD PRIMARY KEY (`project_id`,`supplier_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_user_supplier` (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `asset_usage_logs`
--
ALTER TABLE `asset_usage_logs`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=361;

--
-- AUTO_INCREMENT for table `maintenance_history`
--
ALTER TABLE `maintenance_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `maintenance_schedules`
--
ALTER TABLE `maintenance_schedules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `procurement_requests`
--
ALTER TABLE `procurement_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `asset_forecast_cache`
--
ALTER TABLE `asset_forecast_cache`
  ADD CONSTRAINT `asset_forecast_cache_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `asset_usage_logs`
--
ALTER TABLE `asset_usage_logs`
  ADD CONSTRAINT `asset_usage_logs_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bids`
--
ALTER TABLE `bids`
  ADD CONSTRAINT `bids_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bids_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_forecast_cache`
--
ALTER TABLE `inventory_forecast_cache`
  ADD CONSTRAINT `inventory_forecast_cache_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD CONSTRAINT `inventory_history_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_history`
--
ALTER TABLE `maintenance_history`
  ADD CONSTRAINT `maintenance_history_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_schedules`
--
ALTER TABLE `maintenance_schedules`
  ADD CONSTRAINT `maintenance_schedules_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `procurement_requests`
--
ALTER TABLE `procurement_requests`
  ADD CONSTRAINT `procurement_requests_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_resources`
--
ALTER TABLE `project_resources`
  ADD CONSTRAINT `project_resources_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_resources_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
