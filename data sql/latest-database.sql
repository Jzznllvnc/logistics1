-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 27, 2025 at 06:55 AM
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
(1, 'Low', 'Feb 22, 2026', '2025-08-26 15:46:29'),
(2, 'Low', 'Nov 20, 2026', '2025-08-26 15:49:10'),
(3, 'Low', 'Nov 21, 2025', '2025-08-26 15:46:29'),
(4, 'Low', 'Dec 10, 2025', '2025-08-26 15:46:29'),
(5, 'Medium', 'Nov 15, 2025', '2025-08-26 15:46:29'),
(6, 'Medium', 'May 10, 2026', '2025-08-26 15:46:29'),
(7, 'Low', 'Feb 20, 2026', '2025-08-26 15:46:29');

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
(4, 'tape', 23, '2025-08-21 16:19:06'),
(5, 'Standard Pallets (48x40)', 150, '2025-08-21 16:15:36'),
(6, 'Euro Pallets (1200x800)', 80, '2025-08-21 16:15:36'),
(7, 'Heat Treated Pallets (ISPM-15)', 65, '2025-08-21 16:15:36'),
(8, 'Shrink Wrap Rolls', 320, '2025-08-25 08:42:25'),
(9, 'Packing Tape Rolls', 400, '2025-08-21 16:15:36'),
(10, 'Cardboard Boxes (Large)', 800, '2025-08-21 16:15:36'),
(11, 'Cardboard Boxes (Medium)', 1200, '2025-08-21 16:15:36'),
(12, 'Cardboard Boxes (Small)', 1500, '2025-08-21 16:15:36'),
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
(4, 'API Error', 'Error', '2025-08-26 17:03:17'),
(5, 'API Error', 'Error', '2025-08-26 17:03:17'),
(6, 'Euro Pallets stock is steadily declining.', 'Reorder Soon', '2025-08-26 13:26:12'),
(7, 'Heat Treated Pallets stock is declining, with a period of stability.', 'Reorder Soon', '2025-08-26 13:26:12'),
(8, 'API Error', 'Error', '2025-08-26 17:03:17'),
(9, 'Packing Tape Rolls stock shows a recent significant increase after a period of decline.', 'Monitor Stock', '2025-08-26 13:26:12'),
(10, 'Cardboard Boxes (Large) stock is consistently declining.', 'Expedite Reorder', '2025-08-26 13:26:12'),
(11, 'Cardboard Boxes (Medium) stock shows a recent increase after a period of decline.', 'Monitor Stock', '2025-08-26 13:26:12'),
(12, 'Cardboard Boxes (Small) stock is slowly decreasing.', 'Monitor Stock', '2025-08-26 13:26:12'),
(13, 'Bubble Wrap Rolls stock is rapidly depleting.', 'Expedite Reorder', '2025-08-26 13:26:12'),
(14, 'API Error', 'Error', '2025-08-26 17:03:17'),
(15, 'Bill of Lading Forms stock is steadily decreasing.', 'Reorder Soon', '2025-08-26 13:26:12'),
(17, 'Cargo Straps stock is steadily increasing.', 'Monitor Stock', '2025-08-26 13:26:12'),
(18, 'Safety Box Cutters stock is slowly decreasing.', 'Monitor Stock', '2025-08-26 13:26:12'),
(19, 'API Error', 'Error', '2025-08-26 17:03:17');

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
(244, 19, 90, '2025-07-26 09:06:00');

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

--
-- Dumping data for table `project_resources`
--

INSERT INTO `project_resources` (`project_id`, `supplier_id`) VALUES
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int NOT NULL,
  `supplier_id` int NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `supplier_id`, `item_name`, `quantity`, `status`, `order_date`) VALUES
(1, 2, 'tape', 23123, 'Pending', '2025-08-21 16:33:45'),
(2, 2, 'Cargo Straps', 400, 'Pending', '2025-08-25 11:05:07'),
(3, 2, 'Euro Pallets (1200x800)', 12, 'Pending', '2025-08-26 15:55:52'),
(4, 4, 'Flashlight', 10, 'Pending', '2025-08-26 15:58:24');

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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `email`, `phone`, `address`, `created_at`) VALUES
(1, 'Global Freight Forwarders', 'John Davis', 'john.d@gff.com', '+1-202-555-0171', '123 Shipping Lane, Long Beach, CA 90802', '2025-08-21 16:28:42'),
(2, 'Express Cargo Inc.', 'Maria Rodriguez', 'maria.r@expresscargo.com', '+44 20 7946 0958', 'Unit 5, Cargo Terminal, Heathrow Airport, UK', '2025-08-21 16:28:42'),
(3, 'Oceanic Transport Co.', 'Wei Chen', 'wei.c@oceanictrans.com', '+65 6749 8888', '70 Shenton Way, #12-01, Singapore 079118', '2025-08-21 16:28:42'),
(4, 'rovic', 'bebe123', 'roviccastrodes@yahoo.com', '099123', '55 vaedrew', '2025-08-21 16:34:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin123', 'admin', '2025-08-21 17:20:50'),
(2, 'warehouse', 'wh123', 'smart_warehousing', '2025-08-21 17:20:50'),
(3, 'procure', 'pr123', 'procurement', '2025-08-21 17:20:50'),
(4, 'pltuser', 'plt123', 'plt', '2025-08-21 17:20:50'),
(5, 'almsuser', 'alms123', 'alms', '2025-08-21 17:20:50'),
(6, 'dtrsuser', 'dtrs123', 'dtrs', '2025-08-21 17:20:50');

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
  ADD UNIQUE KEY `username` (`username`);

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
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=359;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
