-- phpMyAdmin SQL Dump
-- Database: `freight_db`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Use utf8mb4
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Table structure for table `purchase_orders`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(50) NOT NULL,
  `supplier` varchar(100) NOT NULL,
  `order_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `origin` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `cargo_info` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample data
INSERT INTO `purchase_orders` (`id`, `po_number`, `supplier`, `order_date`, `status`, `origin`, `destination`, `cargo_info`) VALUES
(4, '123123123', 'Samuel', '2025-08-31', 'Pending', 'Manila', 'Caloocan', '10 parcel of toys'),
(5, '19823981238', 'Kent Ian', '2025-08-31', 'Pending', 'Manila', 'Bulacan', 'full of kups'),
(6, '1231231245', 'Derrick', '2025-08-31', 'Pending', 'Bataan', 'Pampanga', 'qweqeqeq'),
(7, '6745643323', 'Kobe Bryant', '2025-08-31', 'Pending', 'Fairview', 'Cubao', 'Choper'),
(8, '73434534', 'Kent Ian', '2025-08-31', 'Pending', 'Fairview', 'Cubao', 'samwell kups');

-- --------------------------------------------------------
-- Table structure for table `shipments`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `status` enum('Pending','In Transit','Delivered','Ready','Approved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `consolidated` tinyint(1) DEFAULT 0,
  `archived` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `po_id` (`po_id`),
  CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample data
INSERT INTO `shipments` (`id`, `po_id`, `driver_name`, `vehicle_number`, `status`, `created_at`, `consolidated`, `archived`) VALUES
(5, 5, 'Samuel Adams', '232ASD', 'Delivered', '2025-08-31 08:01:09', 1, 0),
(6, 5, 'qweqwe', 'qweq', 'Delivered', '2025-08-31 08:01:50', 1, 1),
(7, 7, 'Khem Gicana', '564AQS', 'In Transit', '2025-08-31 08:17:42', 0, 0),
(8, 6, 'Samuel Adams', '232ASD', 'Ready', '2025-08-31 08:18:05', 0, 0),
(9, 8, 'Khem Gicana', '564AQS', 'Ready', '2025-08-31 08:19:28', 0, 0);

-- --------------------------------------------------------
-- Table structure for table `bills_of_lading`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bills_of_lading` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bl_number` varchar(50) NOT NULL,
  `type` ENUM('HBL','MBL') NOT NULL,
  `shipper` VARCHAR(255),
  `consignee` VARCHAR(255),
  `origin` VARCHAR(255),
  `destination` VARCHAR(255),
  `shipment_id` int(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`shipment_id`) REFERENCES shipments(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `consolidations`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `consolidations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample data
INSERT INTO `consolidations` (`id`, `created_at`) VALUES
(1, '2025-08-31 07:49:08'),
(2, '2025-08-31 08:01:19'),
(3, '2025-08-31 08:01:57');

-- --------------------------------------------------------
-- Table structure for table `consolidation_shipments`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `consolidation_shipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consolidation_id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `consolidation_id` (`consolidation_id`),
  KEY `shipment_id` (`shipment_id`),
  CONSTRAINT `consolidation_shipments_ibfk_1` FOREIGN KEY (`consolidation_id`) REFERENCES `consolidations`(`id`) ON DELETE CASCADE,
  CONSTRAINT `consolidation_shipments_ibfk_2` FOREIGN KEY (`shipment_id`) REFERENCES `shipments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample data
INSERT INTO `consolidation_shipments` (`id`, `consolidation_id`, `shipment_id`) VALUES
(2, 2, 5),
(3, 3, 6);

-- --------------------------------------------------------
-- Table structure for table `deconsolidations`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `deconsolidations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consolidation_id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `deconsolidated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consolidation_id` (`consolidation_id`),
  KEY `shipment_id` (`shipment_id`),
  CONSTRAINT `deconsolidations_ibfk_1` FOREIGN KEY (`consolidation_id`) REFERENCES `consolidations` (`id`),
  CONSTRAINT `deconsolidations_ibfk_2` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `shipment_bookings`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shipment_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `booking_date` date NOT NULL,
  `status` enum('Pending','In Transit','Delivered','Cancelled') DEFAULT 'Pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `shipment_tracking`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shipment_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipment_id` int(11) NOT NULL,
  `location` varchar(100) NOT NULL,
  `status` enum('At Origin','In Transit','At Warehouse','Delivered','Delayed') DEFAULT 'In Transit',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
