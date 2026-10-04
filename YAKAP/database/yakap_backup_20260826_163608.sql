-- YAKAP database backup
-- Generated: 2026-08-26 16:36:08

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(80) NOT NULL,
  `record_id` varchar(80) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `backup_records`;
CREATE TABLE `backup_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `backup_records_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `medical_records`;
CREATE TABLE `medical_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `physician` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `record_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `medicine_inventory`;
CREATE TABLE `medicine_inventory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `medicine_id` int(10) unsigned NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 10,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `medicine_inventory_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medicine_inventory` VALUES ('1','1','999','10');
INSERT INTO `medicine_inventory` VALUES ('2','3','50','10');
INSERT INTO `medicine_inventory` VALUES ('4','4','922','10');
INSERT INTO `medicine_inventory` VALUES ('5','5','100','20');
INSERT INTO `medicine_inventory` VALUES ('6','6','75','15');
INSERT INTO `medicine_inventory` VALUES ('7','7','1000','10');

DROP TABLE IF EXISTS `medicine_purchase_items`;
CREATE TABLE `medicine_purchase_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` int(10) unsigned NOT NULL,
  `medicine_id` int(10) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `medicine_purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `medicine_purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicine_purchase_items_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medicine_purchase_items` VALUES ('1','1','1','1','2.00','2.00');
INSERT INTO `medicine_purchase_items` VALUES ('2','2','3','1','8.00','8.00');
INSERT INTO `medicine_purchase_items` VALUES ('3','3','4','78','3.50','273.00');

DROP TABLE IF EXISTS `medicine_purchases`;
CREATE TABLE `medicine_purchases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_no` varchar(40) NOT NULL,
  `patient_id` int(10) unsigned NOT NULL,
  `pharmacist_id` int(10) unsigned DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reference_no` varchar(80) DEFAULT NULL,
  `purchase_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_no` (`transaction_no`),
  KEY `patient_id` (`patient_id`),
  KEY `pharmacist_id` (`pharmacist_id`),
  CONSTRAINT `medicine_purchases_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `medicine_purchases_ibfk_2` FOREIGN KEY (`pharmacist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medicine_purchases` VALUES ('1','TXN-20260826120838379','1','1','2.00','OPD000123456','2026-08-26 18:08:38');
INSERT INTO `medicine_purchases` VALUES ('2','TXN-20260826160459247','1','1','8.00','OPD000123456','2026-08-26 22:04:59');
INSERT INTO `medicine_purchases` VALUES ('3','TXN-20260826160510946','1','1','273.00','OPD000123456','2026-08-26 22:05:10');

DROP TABLE IF EXISTS `medicine_stock_movements`;
CREATE TABLE `medicine_stock_movements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `medicine_id` int(10) unsigned NOT NULL,
  `movement_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `batch_number` varchar(80) DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `medicine_id` (`medicine_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `medicine_stock_movements_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicine_stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medicine_stock_movements` VALUES ('1','3','ADJUSTMENT','1000',NULL,NULL,'Stock increased through inventory edit.','1','2026-08-26 22:04:26');
INSERT INTO `medicine_stock_movements` VALUES ('3','4','ADJUSTMENT','1000',NULL,NULL,'Stock increased through inventory edit.','1','2026-08-26 22:04:38');

DROP TABLE IF EXISTS `medicines`;
CREATE TABLE `medicines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `medicine_name` varchar(150) NOT NULL,
  `generic_name` varchar(150) DEFAULT NULL,
  `dosage_form` varchar(80) DEFAULT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medicines` VALUES ('1','Paracetamol','Biogesic','Tablet','2.00','Active','2026-08-26 17:54:48');
INSERT INTO `medicines` VALUES ('3','Amoxicillin 500mg','Amoxicillin','Capsule','8.50','Active','2026-08-26 21:57:26');
INSERT INTO `medicines` VALUES ('4','Salbutamol 2mg','Salbutamol','Tablet','3.50','Active','2026-08-26 21:57:26');
INSERT INTO `medicines` VALUES ('5','Paracetamol 500mg','Paracetamol','Tablet','2.50','Active','2026-08-26 22:30:52');
INSERT INTO `medicines` VALUES ('6','Ibuprofen 400mg','Ibuprofen','Tablet','5.00','Active','2026-08-26 22:30:52');
INSERT INTO `medicines` VALUES ('7','test','test','test','12.00','Active','2026-08-26 22:34:15');

DROP TABLE IF EXISTS `patients`;
CREATE TABLE `patients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_code` varchar(50) NOT NULL,
  `philhealth_id` varchar(30) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `middle_name` varchar(80) DEFAULT NULL,
  `last_name` varchar(80) NOT NULL,
  `birthdate` date NOT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_code` (`patient_code`),
  KEY `idx_philhealth` (`philhealth_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `patients` VALUES ('1','0123456789','0123456789','Eusebio','Delima','Ladrica','1976-09-29','Male','24 Hereford St., Project 8, Quezon City','09174446583','Active','2026-08-26 17:48:00');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role` enum('Admin','Pharmacist','Encoder') NOT NULL DEFAULT 'Encoder',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES ('1','admin','$2y$10$YC7GDDq9NeNroDa6jolPf.BeJ0TG9/Hf6t.cUrJGw6gSyfY.e5RtG','Brian','Admin','Active','2026-08-26 17:46:22');
INSERT INTO `users` VALUES ('2','Alyssa','$2y$10$i2p.bS/I.cytxNxaliU1xu5GWsmgaYDzSUlZpa1V2vhSmpfMGEyI2','Alyssa','Encoder','Active','2026-08-26 19:09:00');

SET FOREIGN_KEY_CHECKS=1;
