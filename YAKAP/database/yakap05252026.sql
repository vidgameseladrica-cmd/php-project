-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2026 at 03:01 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yakap`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(80) NOT NULL,
  `record_id` varchar(80) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'CREATE', 'Patient Records', '1234567890', 'Created patient record', '192.168.4.99', '2026-08-25 05:58:10'),
(2, 1, 'CREATE', 'Patient Records', '1122334455', 'Created patient record', '192.168.4.99', '2026-08-25 06:04:00'),
(3, 1, 'UPDATE', 'Patient Records', '1122334455', 'Updated patient information', '192.168.4.99', '2026-08-25 06:11:46');

-- --------------------------------------------------------

--
-- Table structure for table `backup_records`
--

CREATE TABLE `backup_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `backup_records`
--

INSERT INTO `backup_records` (`id`, `filename`, `file_size`, `created_by`, `created_at`) VALUES
(1, 'yakap_backup_20260825_081336.sql', 8886, 1, '2026-08-25 06:13:36'),
(2, 'yakap_backup_20260825_081955.sql', 9205, 1, '2026-08-25 06:19:55'),
(3, 'yakap_backup_20260825_081958.sql', 9316, 1, '2026-08-25 06:19:58'),
(4, 'yakap_backup_20260825_081959.sql', 9427, 1, '2026-08-25 06:19:59'),
(5, 'yakap_backup_20260825_082003.sql', 9538, 1, '2026-08-25 06:20:03');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `patient_id` int(10) UNSIGNED NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `physician` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `record_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(10) UNSIGNED NOT NULL,
  `medicine_name` varchar(150) NOT NULL,
  `generic_name` varchar(150) DEFAULT NULL,
  `dosage_form` varchar(80) DEFAULT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `medicine_name`, `generic_name`, `dosage_form`, `unit_price`, `status`, `created_at`) VALUES
(1, 'AVAMAX', 'LOZARTAN', '', 25.00, 'Active', '2026-08-25 06:00:02'),
(2, 'BIOGESIC', 'PARACETAMOL', '', 5.00, 'Active', '2026-08-25 06:01:40'),
(3, 'Urinalysis', 'Urinalysis', '', 120.00, 'Active', '2026-08-25 06:59:37'),
(4, 'FPE', 'FIRST PATIENT ENCOUNTER', '', 680.00, 'Active', '2026-08-25 07:35:48');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_inventory`
--

CREATE TABLE `medicine_inventory` (
  `id` int(10) UNSIGNED NOT NULL,
  `medicine_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_inventory`
--

INSERT INTO `medicine_inventory` (`id`, `medicine_id`, `quantity`, `reorder_level`) VALUES
(1, 1, 0, 10),
(2, 2, 77, 100),
(3, 3, 98, 100),
(4, 4, 999, 1000);

-- --------------------------------------------------------

--
-- Table structure for table `medicine_purchases`
--

CREATE TABLE `medicine_purchases` (
  `id` int(10) UNSIGNED NOT NULL,
  `transaction_no` varchar(40) NOT NULL,
  `patient_id` int(10) UNSIGNED NOT NULL,
  `pharmacist_id` int(10) UNSIGNED DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reference_no` varchar(80) DEFAULT NULL,
  `purchase_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_purchases`
--

INSERT INTO `medicine_purchases` (`id`, `transaction_no`, `patient_id`, `pharmacist_id`, `total_amount`, `reference_no`, `purchase_date`) VALUES
(1, 'TXN-20260825080019511', 1, 1, 25.00, '', '2026-08-25 14:00:19'),
(2, 'TXN-20260825080151644', 1, 1, 5.00, '', '2026-08-25 14:01:51'),
(3, 'TXN-20260825080440803', 2, 1, 10.00, '', '2026-08-25 14:04:40'),
(4, 'TXN-20260825081433847', 1, 1, 30.00, '', '2026-08-25 14:14:33'),
(5, 'TXN-20260825085956885', 1, 1, 120.00, '', '2026-08-25 14:59:56'),
(6, 'TXN-20260825090322511', 3, 1, 10.00, '', '2026-08-25 15:03:22'),
(7, 'TXN-20260825090330941', 3, 1, 60.00, '', '2026-08-25 15:03:30'),
(8, 'TXN-20260825090340234', 3, 1, 120.00, '', '2026-08-25 15:03:40'),
(9, 'TXN-20260825093601400', 3, 1, 680.00, '', '2026-08-25 15:36:01');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_purchase_items`
--

CREATE TABLE `medicine_purchase_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `purchase_id` int(10) UNSIGNED NOT NULL,
  `medicine_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_purchase_items`
--

INSERT INTO `medicine_purchase_items` (`id`, `purchase_id`, `medicine_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 1, 1, 25.00, 25.00),
(2, 2, 2, 1, 5.00, 5.00),
(3, 3, 2, 2, 5.00, 10.00),
(4, 4, 2, 6, 5.00, 30.00),
(5, 5, 3, 1, 120.00, 120.00),
(6, 6, 2, 2, 5.00, 10.00),
(7, 7, 2, 12, 5.00, 60.00),
(8, 8, 3, 1, 120.00, 120.00),
(9, 9, 4, 1, 680.00, 680.00);

-- --------------------------------------------------------

--
-- Table structure for table `medicine_stock_movements`
--

CREATE TABLE `medicine_stock_movements` (
  `id` int(10) UNSIGNED NOT NULL,
  `medicine_id` int(10) UNSIGNED NOT NULL,
  `movement_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `batch_number` varchar(80) DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(10) UNSIGNED NOT NULL,
  `patient_code` varchar(30) NOT NULL,
  `philhealth_id` varchar(30) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `middle_name` varchar(80) DEFAULT NULL,
  `last_name` varchar(80) NOT NULL,
  `birthdate` date NOT NULL,
  `age` int(10) UNSIGNED DEFAULT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patient_code`, `philhealth_id`, `first_name`, `middle_name`, `last_name`, `birthdate`, `age`, `sex`, `address`, `contact_number`, `status`, `created_at`) VALUES
(1, '1234567890', '0987654321', 'EUSEBIO', 'DELIMA', 'LADRICA', '1976-05-29', NULL, 'Male', 'QUEZON CTY', '09174446583', 'Active', '2026-08-25 05:58:10'),
(2, '1122334455', '1122334455', 'MARICEL', '', 'VILLOCILLO', '1976-01-21', NULL, 'Female', 'SAN JOSE DEL MONTE', '123456', 'Active', '2026-08-25 06:04:00'),
(3, '123456', '123456', 'Romabelle', 'Aguilar', 'Peralta', '1987-01-23', NULL, 'Female', 'Quezon City', '09174446583', 'Active', '2026-08-25 07:01:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role` enum('Admin','Pharmacist','Encoder') NOT NULL DEFAULT 'Encoder',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$12$A2396c2feORckMAwO/W8luzBoZXLXzkoQWkfQDtQ46QZeFhn9l9Di', 'System Administrator', 'Admin', 'Active', '2026-08-25 05:30:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `backup_records`
--
ALTER TABLE `backup_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medicine_inventory`
--
ALTER TABLE `medicine_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `medicine_purchases`
--
ALTER TABLE `medicine_purchases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_no` (`transaction_no`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `pharmacist_id` (`pharmacist_id`);

--
-- Indexes for table `medicine_purchase_items`
--
ALTER TABLE `medicine_purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `medicine_stock_movements`
--
ALTER TABLE `medicine_stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_code` (`patient_code`),
  ADD KEY `idx_philhealth` (`philhealth_id`);

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
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `backup_records`
--
ALTER TABLE `backup_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medicine_inventory`
--
ALTER TABLE `medicine_inventory`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medicine_purchases`
--
ALTER TABLE `medicine_purchases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `medicine_purchase_items`
--
ALTER TABLE `medicine_purchase_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `medicine_stock_movements`
--
ALTER TABLE `medicine_stock_movements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `backup_records`
--
ALTER TABLE `backup_records`
  ADD CONSTRAINT `backup_records_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `medicine_inventory`
--
ALTER TABLE `medicine_inventory`
  ADD CONSTRAINT `medicine_inventory_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medicine_purchases`
--
ALTER TABLE `medicine_purchases`
  ADD CONSTRAINT `medicine_purchases_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `medicine_purchases_ibfk_2` FOREIGN KEY (`pharmacist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `medicine_purchase_items`
--
ALTER TABLE `medicine_purchase_items`
  ADD CONSTRAINT `medicine_purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `medicine_purchases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medicine_purchase_items_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`);

--
-- Constraints for table `medicine_stock_movements`
--
ALTER TABLE `medicine_stock_movements`
  ADD CONSTRAINT `medicine_stock_movements_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medicine_stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
