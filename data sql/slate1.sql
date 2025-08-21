-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 21, 2025 at 06:07 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_name`, `asset_type`, `purchase_date`, `status`, `created_at`) VALUES
(1, 'Forklift #1', 'Material Handling', '2023-05-15', 'Operational', '2025-08-21 17:42:03'),
(2, 'Delivery Truck - D07', 'Vehicle', '2022-11-20', 'Operational', '2025-08-21 17:42:03'),
(3, 'Pallet Jack - A', 'Material Handling', '2024-01-30', 'Under Maintenance', '2025-08-21 17:42:03'),
(4, 'Warehouse Conveyor Belt', 'Equipment', '2021-08-01', 'Operational', '2025-08-21 17:42:03');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `quantity`, `last_updated`) VALUES
(4, 'tape', 23, '2025-08-21 16:19:06'),
(5, 'Standard Pallets (48x40)', 150, '2025-08-21 16:15:36'),
(6, 'Euro Pallets (1200x800)', 80, '2025-08-21 16:15:36'),
(7, 'Heat Treated Pallets (ISPM-15)', 65, '2025-08-21 16:15:36'),
(8, 'Shrink Wrap Rolls', 250, '2025-08-21 16:15:36'),
(9, 'Packing Tape Rolls', 400, '2025-08-21 16:15:36'),
(10, 'Cardboard Boxes (Large)', 800, '2025-08-21 16:15:36'),
(11, 'Cardboard Boxes (Medium)', 1200, '2025-08-21 16:15:36'),
(12, 'Cardboard Boxes (Small)', 1500, '2025-08-21 16:15:36'),
(13, 'Bubble Wrap Rolls', 75, '2025-08-21 16:15:36'),
(14, 'Shipping Labels (Pack of 1000)', 50, '2025-08-21 16:15:36'),
(15, 'Bill of Lading Forms (Pack of 500)', 25, '2025-08-21 16:15:36'),
(16, 'Packing Peanuts (Large Bag)', 40, '2025-08-21 16:15:36'),
(17, 'Cargo Straps', 120, '2025-08-21 16:15:36'),
(18, 'Safety Box Cutters', 30, '2025-08-21 16:15:36'),
(19, 'Work Gloves (Pairs)', 90, '2025-08-21 16:15:36');

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
  `completed_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `maintenance_schedules`
--

INSERT INTO `maintenance_schedules` (`id`, `asset_id`, `task_description`, `scheduled_date`, `status`, `completed_date`) VALUES
(1, 2, 'Engine oil change and tire rotation.', '2025-09-10', 'Completed', '2025-08-21'),
(2, 2, 'asdasd', '2025-08-22', 'Completed', '2025-08-21');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `supplier_id`, `item_name`, `quantity`, `status`, `order_date`) VALUES
(1, 2, 'tape', 23123, 'Pending', '2025-08-21 16:33:45');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Indexes for table `maintenance_schedules`
--
ALTER TABLE `maintenance_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `maintenance_schedules`
--
ALTER TABLE `maintenance_schedules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Constraints for table `maintenance_schedules`
--
ALTER TABLE `maintenance_schedules`
  ADD CONSTRAINT `maintenance_schedules_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

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
