-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 03, 2025 at 04:29 PM
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
-- Database: `agri_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int NOT NULL,
  `street` varchar(150) DEFAULT NULL,
  `purok` varchar(100) DEFAULT NULL,
  `barangay_id` int NOT NULL,
  `municipality` varchar(100) NOT NULL DEFAULT 'Calapan City',
  `province` varchar(100) NOT NULL DEFAULT 'Oriental Mindoro',
  `region` varchar(100) NOT NULL DEFAULT 'MIMAROPA (Region IV-B)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `street`, `purok`, `barangay_id`, `municipality`, `province`, `region`) VALUES
(25, 'Centro', NULL, 3, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(26, 'Centro', NULL, 13, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(27, 'Centro', NULL, 45, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(28, 'Centro', NULL, 19, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(30, 'Sitio Banaba', NULL, 19, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(31, 'Sitio Banaba', NULL, 15, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(32, 'Sitio Bagong Silang', NULL, 19, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(36, 'Sitio Bulaklak', NULL, 1, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(37, 'Sitio taas', NULL, 18, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(38, 'Centro', NULL, 10, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(39, 'Centro', NULL, 10, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(40, 'Langka', NULL, 1, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(41, 'Centro', NULL, 18, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(42, 'Langka', NULL, 9, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(43, 'Centro', NULL, 21, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(44, 'Langka', NULL, 9, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(45, 'Centro', NULL, 16, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(46, 'Langka', NULL, 1, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(47, 'Centro', NULL, 17, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(48, 'Langka', NULL, 1, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)'),
(49, 'Centro', NULL, 32, 'Calapan City', 'Oriental Mindoro', 'MIMAROPA (Region IV-B)');

-- --------------------------------------------------------

--
-- Table structure for table `barangays`
--

CREATE TABLE `barangays` (
  `barangay_id` int NOT NULL,
  `barangay_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barangays`
--

INSERT INTO `barangays` (`barangay_id`, `barangay_name`) VALUES
(1, 'Balingayans'),
(2, 'Balite'),
(3, 'Baruyan'),
(4, 'Batino'),
(5, 'Bayanan I'),
(6, 'Bayanan II'),
(7, 'Biga'),
(8, 'Bondoc'),
(9, 'Bucayao'),
(10, 'Buhuan'),
(11, 'Bulusan'),
(12, 'Calero (Poblacion)'),
(13, 'Camansihan'),
(14, 'Camilmil'),
(15, 'Canubing I'),
(16, 'Canubing II'),
(17, 'Comunal'),
(18, 'Guinobatan'),
(19, 'Gulod'),
(20, 'Gutad'),
(21, 'Ibaba East'),
(22, 'Ibaba West'),
(23, 'Ilaya'),
(24, 'Lalud'),
(25, 'Lazareto'),
(26, 'Libis (Poblacion)'),
(27, 'Lumang Bayan'),
(28, 'Mahal na Pangalan'),
(29, 'Maidlang'),
(30, 'Malad'),
(31, 'Malamig'),
(32, 'Managpi'),
(33, 'Masipit'),
(34, 'Nag-Iba I'),
(35, 'Nag-Iba II'),
(36, 'Navotas'),
(37, 'Pachoca'),
(38, 'Palhi'),
(39, 'Panggalaan'),
(40, 'Parang'),
(41, 'Patas'),
(42, 'Personas'),
(43, 'Putting Tubig'),
(44, 'Salong (San Raphael)'),
(45, 'San Antonio'),
(46, 'San Vicente Central'),
(47, 'San Vicente East'),
(48, 'San Vicente North'),
(49, 'San Vicente South'),
(50, 'San Vicente West'),
(51, 'Santa Cruz'),
(52, 'Santa Isabel'),
(53, 'Santa Maria Village'),
(54, 'Santa Rita'),
(55, 'Santo Niño (Nacoco)'),
(56, 'Sapul'),
(57, 'Silonay'),
(58, 'Suqui'),
(59, 'Tawagan'),
(60, 'Tawiran'),
(61, 'Tibag'),
(62, 'Wawa');

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `category_id` int NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Seeds', NULL),
(2, 'Fertilizer', NULL),
(3, 'Pesticide', NULL),
(4, 'Labor', NULL),
(5, 'Irrigation', NULL),
(6, 'Fuel', NULL),
(7, 'Machinery', NULL),
(8, 'Others', NULL),
(9, 'Herbicide', 'Lason para sa damo o halamang sagabal'),
(10, 'Insecticide', 'Pamatay insekto'),
(11, 'Molluscicide', 'Pamatay suso o snail'),
(12, 'Rodenticide', 'Pamatay daga'),
(13, 'Fungicide', 'Pamatay amag o fungus');

-- --------------------------------------------------------

--
-- Table structure for table `expense_items`
--

CREATE TABLE `expense_items` (
  `item_id` int NOT NULL,
  `category_id` int NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `unit` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `expense_items`
--

INSERT INTO `expense_items` (`item_id`, `category_id`, `item_name`, `unit`) VALUES
(1, 1, 'Certified Seeds', 'kg'),
(2, 2, 'Urea (46-0-0)', 'bag'),
(3, 2, 'Complete (14-14-14)', 'bag'),
(4, 3, 'Insecticide', 'liter'),
(5, 3, 'Herbicide', 'liter'),
(6, 4, 'Land Preparation Labor', 'day'),
(7, 4, 'Harvest Labor', 'day'),
(8, 5, 'Irrigation Fee', 'hectare'),
(9, 6, 'Fuel (Diesel)', 'liter'),
(10, 7, 'Tractor Maintenance', 'service'),
(11, 8, 'Miscellaneous', 'unit');

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

CREATE TABLE `farmers` (
  `farmer_id` int NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_initial` varchar(10) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `place_of_birth` varchar(150) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `citizenship` varchar(50) DEFAULT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `cellphone` varchar(20) DEFAULT NULL,
  `address_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`farmer_id`, `id_number`, `last_name`, `first_name`, `middle_initial`, `birthdate`, `age`, `occupation`, `place_of_birth`, `civil_status`, `citizenship`, `sex`, `cellphone`, `address_id`) VALUES
(1, 'FRM-20251021-98601', 'Tegio', 'Ivy', 'C.', '2000-10-24', NULL, 'Farmer', 'Calapan City', 'Married', 'Filipino', 'Female', '09123456789', 25),
(4, 'FRM-20251026-28398', 'Cagalfin', 'Rafaela', 'B', '1951-10-24', NULL, 'Farmer', 'Banalo, Lobo Batangas ', 'Married', 'Filipino', 'Female', '09817241625', 31),
(5, 'FRM-20251028-68819', 'Borbon', 'Erika', 'C.', '1999-02-28', NULL, 'Farmer', 'Calapan City', '', 'Filipino', 'Male', '09246810121', 36),
(6, 'FRM-20251028-68172', 'Mendoza', 'James', 'M', '1996-02-06', NULL, 'Farmer', 'Calapan City', 'Married', 'Filipino', 'Male', '09246810121', 38),
(8, 'FRM-20251029-45271', 'Plata', 'Jetro', 'M.', '2005-09-14', NULL, 'Farmer', 'Calapan City', '', 'Filipino', 'Male', '09067799044', 42),
(9, 'FRM-20251030-58948', 'Dela Cruz', 'Juan', 'c.', '1959-01-30', NULL, 'Farmer', 'Calapan City', '', 'Filipino', 'Male', '09067799044', 44),
(10, 'FRM-20251030-22676', 'Dela Cruz', 'Juan Jose', 'c.', '1959-01-30', NULL, 'Farmer', 'Calapan City', '', 'Filipino', 'Male', '09067799044', 46),
(11, 'FRM-20251030-22505', 'Adriatico', 'Jasper', 'm', '1997-01-30', NULL, 'Farmer', 'Calapan City', 'Single', 'Filipino', 'Male', '09067799044', 48);

-- --------------------------------------------------------

--
-- Stand-in structure for view `farmer_summary`
-- (See below for the actual view)
--
CREATE TABLE `farmer_summary` (
);

-- --------------------------------------------------------

--
-- Table structure for table `farms`
--

CREATE TABLE `farms` (
  `farm_id` int NOT NULL,
  `farmer_id` int NOT NULL,
  `address_id` int NOT NULL,
  `farm_area` decimal(6,2) DEFAULT NULL,
  `tenurial_status` enum('Owner','Tenant','Lessee','Others') DEFAULT NULL,
  `farm_owner_name` varchar(150) DEFAULT NULL,
  `farm_owner_cell` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `farms`
--

INSERT INTO `farms` (`farm_id`, `farmer_id`, `address_id`, `farm_area`, `tenurial_status`, `farm_owner_name`, `farm_owner_cell`) VALUES
(1, 1, 26, 8.92, 'Owner', '', ''),
(4, 4, 32, 1.20, 'Owner', '', ''),
(5, 5, 37, 1.20, 'Owner', '', ''),
(6, 6, 39, 1.20, 'Owner', '', ''),
(8, 8, 43, 1.20, 'Owner', '', ''),
(9, 9, 45, 5.00, 'Owner', '', ''),
(10, 10, 47, 5.00, 'Owner', '', ''),
(11, 11, 49, 5.00, 'Owner', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `production_expense`
--

CREATE TABLE `production_expense` (
  `expense_id` int NOT NULL,
  `production_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `expense_item` varchar(150) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `expense_date` date DEFAULT (curdate()),
  `remarks` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `production_expense`
--

INSERT INTO `production_expense` (`expense_id`, `production_id`, `category_id`, `expense_item`, `amount`, `expense_date`, `remarks`) VALUES
(92, 18, 1, 'Seeds', 1200.00, '2025-10-30', ''),
(93, 18, 2, 'Fertilizer', 1500.00, '2025-10-30', ''),
(94, 18, 3, 'Pesticide', 500.00, '2025-10-30', ''),
(95, 18, 4, 'Labor', 2000.00, '2025-10-30', ''),
(96, 18, 6, 'Fuel', 2500.00, '2025-10-30', ''),
(97, 18, 7, 'Machinery', 4500.00, '2025-10-30', ''),
(98, 18, 8, 'Herbicide', 600.00, '2025-10-30', ''),
(99, 18, 9, 'Insecticide', 700.00, '2025-10-30', ''),
(100, 18, 10, 'Molluscicide', 300.00, '2025-10-30', ''),
(101, 18, 11, 'Rodenticide', 400.00, '2025-10-30', ''),
(102, 18, 12, 'Fungicide', 450.00, '2025-10-30', ''),
(103, 21, 1, 'Seeds', 100.00, '2025-10-30', ''),
(104, 21, 2, 'Fertilizer', 1000.00, '2025-10-30', ''),
(105, 21, 3, 'Pesticide', 100.00, '2025-10-30', ''),
(106, 21, 4, 'Labor', 100.00, '2025-10-30', ''),
(107, 21, 5, 'Irrigation', 1000.00, '2025-10-30', ''),
(108, 21, 6, 'Fuel', 1000.00, '2025-10-30', ''),
(109, 21, 7, 'Machinery', 1000.00, '2025-10-30', ''),
(110, 21, 8, 'Herbicide', 100.00, '2025-10-30', ''),
(111, 21, 9, 'Insecticide', 100.00, '2025-10-30', ''),
(112, 21, 10, 'Molluscicide', 1000.00, '2025-10-30', ''),
(113, 21, 11, 'Rodenticide', 1000.00, '2025-10-30', ''),
(114, 21, 12, 'Fungicide', 1000.00, '2025-10-30', ''),
(211, 22, 1, 'Seeds', 2100.00, '2025-11-04', ''),
(212, 22, 2, 'Fertilizer', 1000.00, '2025-11-04', ''),
(213, 22, 3, 'Pesticide', 1000.00, '2025-11-04', ''),
(214, 22, 4, 'Labor', 1000.00, '2025-11-04', ''),
(215, 22, 5, 'Irrigation', 1000.00, '2025-11-04', ''),
(216, 22, 6, 'Fuel', 1000.00, '2025-11-04', ''),
(217, 22, 7, 'Machinery', 1000.00, '2025-11-04', ''),
(218, 22, 8, 'Herbicide', 1000.00, '2025-11-04', ''),
(219, 22, 9, 'Insecticide', 1000.00, '2025-11-04', ''),
(220, 22, 10, 'Molluscicide', 1000.00, '2025-11-04', ''),
(221, 22, 11, 'Rodenticide', 1000.00, '2025-11-04', ''),
(222, 22, 12, 'Fungicide', 1000.00, '2025-11-04', '');

-- --------------------------------------------------------

--
-- Table structure for table `production_records`
--

CREATE TABLE `production_records` (
  `production_id` int NOT NULL,
  `farmer_id` int NOT NULL,
  `farm_id` int NOT NULL,
  `season_id` int NOT NULL,
  `crop_type` enum('Palay') NOT NULL DEFAULT 'Palay',
  `sacks_harvested` int DEFAULT '0',
  `weight_per_sack` decimal(6,2) DEFAULT '0.00',
  `planting_method` enum('Sabog','Bunot at Talok','Transplant','Direct Seeding') DEFAULT 'Sabog',
  `irrigation_method` enum('NIA','Bugsok/Water Pump','Manual','Rainfed') DEFAULT 'NIA',
  `planting_date` date DEFAULT NULL,
  `harvest_date` date DEFAULT NULL,
  `yield_kg` decimal(10,2) DEFAULT '0.00',
  `selling_price` decimal(10,2) DEFAULT '0.00',
  `gross_income` decimal(12,2) GENERATED ALWAYS AS ((`yield_kg` * `selling_price`)) STORED,
  `net_income` decimal(12,2) GENERATED ALWAYS AS (((`yield_kg` * `selling_price`) - `total_expense`)) STORED,
  `notes` text,
  `gross_sales` decimal(12,2) GENERATED ALWAYS AS ((`yield_kg` * `selling_price`)) STORED,
  `total_expense` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `production_records`
--

INSERT INTO `production_records` (`production_id`, `farmer_id`, `farm_id`, `season_id`, `crop_type`, `sacks_harvested`, `weight_per_sack`, `planting_method`, `irrigation_method`, `planting_date`, `harvest_date`, `yield_kg`, `selling_price`, `notes`, `total_expense`, `created_at`) VALUES
(18, 8, 8, 2, 'Palay', 200, 50.00, 'Sabog', 'NIA', '2025-01-30', '2025-05-30', 10000.00, 20.00, '', 14650.00, '2025-10-29 16:35:11'),
(21, 5, 5, 2, 'Palay', 200, 50.00, 'Sabog', 'NIA', '2025-01-30', '2025-05-30', 10000.00, 20.00, '', 7500.00, '2025-10-29 18:03:43'),
(22, 10, 10, 1, 'Palay', 200, 50.00, 'Bunot at Talok', 'Bugsok/Water Pump', '2025-07-24', '2025-10-21', 10000.00, 20.00, '', 13100.00, '2025-10-30 03:14:00'),
(24, 11, 11, 1, 'Palay', 120, 50.00, 'Bunot at Talok', 'Bugsok/Water Pump', '2025-05-24', '2025-10-24', 6000.00, 20.00, 'haha', 0.00, '2025-10-30 08:31:54');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int NOT NULL,
  `production_id` int NOT NULL,
  `quantity_sold` decimal(10,2) DEFAULT '0.00',
  `price_per_kg` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(12,2) GENERATED ALWAYS AS ((`quantity_sold` * `price_per_kg`)) STORED,
  `sale_date` date DEFAULT (curdate()),
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `production_id`, `quantity_sold`, `price_per_kg`, `sale_date`, `remarks`) VALUES
(3, 18, 150.00, 0.00, '2025-05-30', NULL),
(6, 21, 150.00, 0.00, '2025-05-30', NULL),
(7, 22, 200.00, 0.00, '2025-10-30', NULL),
(9, 24, 100.00, 0.00, '2025-10-24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

CREATE TABLE `seasons` (
  `season_id` int NOT NULL,
  `season_name` enum('Dry','Wet') NOT NULL,
  `year` year NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `seasons`
--

INSERT INTO `seasons` (`season_id`, `season_name`, `year`) VALUES
(1, 'Wet', '2025'),
(2, 'Dry', '2025'),
(3, 'Wet', '2026'),
(4, 'Dry', '2026');

-- --------------------------------------------------------

--
-- Table structure for table `technician_barangays`
--

CREATE TABLE `technician_barangays` (
  `id` int NOT NULL,
  `technician_id` int NOT NULL,
  `barangay_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `technician_barangays`
--

INSERT INTO `technician_barangays` (`id`, `technician_id`, `barangay_id`) VALUES
(1, 1, 7),
(4, 4, 4),
(6, 7, 6),
(7, 3, 1),
(8, 7, 11),
(9, 2, 9),
(13, 8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_initial` varchar(10) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','technician') NOT NULL DEFAULT 'technician',
  `must_change_password` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `last_name`, `first_name`, `middle_initial`, `username`, `email`, `password_hash`, `role`, `must_change_password`, `created_at`) VALUES
(1, 'Tiburan', 'Judy Anne', NULL, 'tiburan1234', 'tiburan@gmail.com', '$2y$10$223qPjSwW1DLldZjaiKcCO8u6JT0PF.ZFI9fnF4BwIBsrhOQI1nt.', 'technician', 1, '2025-10-23 13:14:04'),
(2, 'Adriatico', 'Jasper', NULL, 'bossjaps', 'jasper1234@gmail.com', '$2y$10$zAVU/bM6xKM1t0kyToBsKuG0Rak4xTCmil1QKrt8QcqmBtehSoI9O', 'technician', 1, '2025-10-27 06:02:44'),
(3, 'Raquem', 'Jessa May', NULL, 'jessamay', 'jessamay@gmail.com', '$2y$10$ElqhNCblzBJUV1qSh3Q8gOUFY.bBQXDaWBuZ9Lip/gKz5LV8tG5wC', 'technician', 1, '2025-10-28 15:20:11'),
(4, 'Siscar', 'Jane', NULL, 'janesiscar123', 'janesiscar@gmail.com', '$2y$10$csc35Qtjl.Yw2Mh8hCu0yen.11ZylWj0g.gRGw7/uT5mb4sU9Jkie', 'technician', 1, '2025-10-28 17:09:13'),
(5, '', '', NULL, 'ivyerika', 'ivyerikategio@gmail.com', 'ivyerikategio', 'admin', 1, '2025-10-29 03:49:10'),
(7, 'Mante Maria', 'Grace', NULL, 'gracemante', 'gracemante@gmail.com', '$2y$10$X9ZJhTQ0Ogjyzsw1sgdJI.niOgoq60gGu0nXNDW3kN9PyO.FPk0Zy', 'technician', 1, '2025-10-29 06:36:03'),
(8, 'Delizo', 'Jeramiah', NULL, 'jeremiah', 'jeremiah@gmail.com', '$2y$10$Txpk/rpEKtR3UZIjxwsd7.ImifHoWnNfb/cGHZ4xfPwoRbUNSw.lC', 'technician', 1, '2025-10-30 08:34:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `barangays`
--
ALTER TABLE `barangays`
  ADD PRIMARY KEY (`barangay_id`),
  ADD UNIQUE KEY `barangay_name` (`barangay_name`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `expense_items`
--
ALTER TABLE `expense_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`farmer_id`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `farms`
--
ALTER TABLE `farms`
  ADD PRIMARY KEY (`farm_id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `production_expense`
--
ALTER TABLE `production_expense`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `production_id` (`production_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `production_records`
--
ALTER TABLE `production_records`
  ADD PRIMARY KEY (`production_id`),
  ADD KEY `farm_id` (`farm_id`),
  ADD KEY `season_id` (`season_id`),
  ADD KEY `fk_farmer_production` (`farmer_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `production_id` (`production_id`);

--
-- Indexes for table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`season_id`);

--
-- Indexes for table `technician_barangays`
--
ALTER TABLE `technician_barangays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `technician_id` (`technician_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `barangays`
--
ALTER TABLE `barangays`
  MODIFY `barangay_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `expense_items`
--
ALTER TABLE `expense_items`
  MODIFY `item_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `farmer_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `farms`
--
ALTER TABLE `farms`
  MODIFY `farm_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `production_expense`
--
ALTER TABLE `production_expense`
  MODIFY `expense_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT for table `production_records`
--
ALTER TABLE `production_records`
  MODIFY `production_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `season_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `technician_barangays`
--
ALTER TABLE `technician_barangays`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

-- --------------------------------------------------------

--
-- Structure for view `farmer_summary`
--
DROP TABLE IF EXISTS `farmer_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `farmer_summary`  AS SELECT `f`.`farmer_id` AS `farmer_id`, concat(`f`.`first_name`,' ',`f`.`middle_initial`,' ',`f`.`last_name`) AS `full_name`, `b1`.`barangay_name` AS `farmer_barangay`, `a1`.`street` AS `farmer_street`, `fr`.`farm_id` AS `farm_id`, `b2`.`barangay_name` AS `farm_barangay`, `a2`.`street` AS `farm_street`, `fr`.`farm_area` AS `farm_area`, `fr`.`tenurial_status` AS `tenurial_status`, `s`.`season_name` AS `season_name`, `s`.`year` AS `year`, `p`.`crop_type` AS `crop_type`, `p`.`yield_kg` AS `yield_kg`, `p`.`selling_price` AS `selling_price`, `p`.`gross_sales` AS `gross_sales`, ifnull(sum(`pe`.`total_cost`),0) AS `total_expense`, (`p`.`gross_sales` - ifnull(sum(`pe`.`total_cost`),0)) AS `net_income` FROM ((((((((`farmers` `f` join `addresses` `a1` on((`f`.`address_id` = `a1`.`address_id`))) join `barangays` `b1` on((`a1`.`barangay_id` = `b1`.`barangay_id`))) join `farms` `fr` on((`f`.`farmer_id` = `fr`.`farmer_id`))) join `addresses` `a2` on((`fr`.`address_id` = `a2`.`address_id`))) join `barangays` `b2` on((`a2`.`barangay_id` = `b2`.`barangay_id`))) join `production_records` `p` on((`fr`.`farm_id` = `p`.`farm_id`))) join `seasons` `s` on((`p`.`season_id` = `s`.`season_id`))) left join `production_expenses` `pe` on((`p`.`production_id` = `pe`.`production_id`))) GROUP BY `f`.`farmer_id`, `fr`.`farm_id`, `p`.`production_id` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`barangay_id`) ON DELETE CASCADE;

--
-- Constraints for table `expense_items`
--
ALTER TABLE `expense_items`
  ADD CONSTRAINT `expense_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `farmers`
--
ALTER TABLE `farmers`
  ADD CONSTRAINT `farmers_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`) ON DELETE CASCADE;

--
-- Constraints for table `farms`
--
ALTER TABLE `farms`
  ADD CONSTRAINT `farms_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `farms_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`) ON DELETE CASCADE;

--
-- Constraints for table `production_expense`
--
ALTER TABLE `production_expense`
  ADD CONSTRAINT `production_expense_ibfk_1` FOREIGN KEY (`production_id`) REFERENCES `production_records` (`production_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `production_expense_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `production_records`
--
ALTER TABLE `production_records`
  ADD CONSTRAINT `fk_farmer_production` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_records_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`farm_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_records_ibfk_2` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`season_id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`production_id`) REFERENCES `production_records` (`production_id`) ON DELETE CASCADE;

--
-- Constraints for table `technician_barangays`
--
ALTER TABLE `technician_barangays`
  ADD CONSTRAINT `technician_barangays_ibfk_1` FOREIGN KEY (`technician_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `technician_barangays_ibfk_2` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`barangay_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
