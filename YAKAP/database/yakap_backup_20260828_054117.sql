-- YAKAP database backup
-- Generated: 2026-08-28 05:41:17

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `audit_logs` VALUES ('1','1','CREATE','Patient Records','1234567890','Created patient record','192.168.4.99','2026-08-25 13:58:10');
INSERT INTO `audit_logs` VALUES ('2','1','CREATE','Patient Records','1122334455','Created patient record','192.168.4.99','2026-08-25 14:04:00');
INSERT INTO `audit_logs` VALUES ('3','1','UPDATE','Patient Records','1122334455','Updated patient information','192.168.4.99','2026-08-25 14:11:46');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `backup_records` VALUES ('1','yakap_backup_20260825_081336.sql','8886','1','2026-08-25 14:13:36');
INSERT INTO `backup_records` VALUES ('2','yakap_backup_20260825_081955.sql','9205','1','2026-08-25 14:19:55');
INSERT INTO `backup_records` VALUES ('3','yakap_backup_20260825_081958.sql','9316','1','2026-08-25 14:19:58');
INSERT INTO `backup_records` VALUES ('4','yakap_backup_20260825_081959.sql','9427','1','2026-08-25 14:19:59');
INSERT INTO `backup_records` VALUES ('5','yakap_backup_20260825_082003.sql','9538','1','2026-08-25 14:20:03');

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medicine_inventory` VALUES ('6','6','91','20');
INSERT INTO `medicine_inventory` VALUES ('7','7','37','10');
INSERT INTO `medicine_inventory` VALUES ('8','8','65','15');

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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medicine_purchase_items` VALUES ('10','10','7','1','8.50','8.50');
INSERT INTO `medicine_purchase_items` VALUES ('11','10','8','1','5.00','5.00');
INSERT INTO `medicine_purchase_items` VALUES ('12','10','6','1','2.50','2.50');
INSERT INTO `medicine_purchase_items` VALUES ('13','12','7','3','8.50','25.50');
INSERT INTO `medicine_purchase_items` VALUES ('14','12','8','4','5.00','20.00');
INSERT INTO `medicine_purchase_items` VALUES ('15','12','6','3','2.50','7.50');
INSERT INTO `medicine_purchase_items` VALUES ('16','13','7','3','8.50','25.50');
INSERT INTO `medicine_purchase_items` VALUES ('17','13','8','3','5.00','15.00');
INSERT INTO `medicine_purchase_items` VALUES ('18','13','6','3','2.50','7.50');
INSERT INTO `medicine_purchase_items` VALUES ('19','14','7','5','8.50','42.50');
INSERT INTO `medicine_purchase_items` VALUES ('20','14','6','1','2.50','2.50');
INSERT INTO `medicine_purchase_items` VALUES ('21','14','8','1','5.00','5.00');
INSERT INTO `medicine_purchase_items` VALUES ('22','15','7','1','8.50','8.50');
INSERT INTO `medicine_purchase_items` VALUES ('23','15','8','1','5.00','5.00');
INSERT INTO `medicine_purchase_items` VALUES ('24','15','6','1','2.50','2.50');

DROP TABLE IF EXISTS `medicine_purchases`;
CREATE TABLE `medicine_purchases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_no` varchar(40) NOT NULL,
  `patient_id` int(10) unsigned NOT NULL,
  `pharmacist_id` int(10) unsigned DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `reference_no` varchar(80) DEFAULT NULL,
  `purchase_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_no` (`transaction_no`),
  KEY `patient_id` (`patient_id`),
  KEY `pharmacist_id` (`pharmacist_id`),
  CONSTRAINT `medicine_purchases_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `medicine_purchases_ibfk_2` FOREIGN KEY (`pharmacist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medicine_purchases` VALUES ('10','','1',NULL,'16.00','1','OPD00012','2026-08-28 00:00:00');
INSERT INTO `medicine_purchases` VALUES ('12','TXN-20260828-053444-8061','3',NULL,'53.00','1','OPD123','2026-08-28 00:00:00');
INSERT INTO `medicine_purchases` VALUES ('13','TXN-20260828-053520-4189','2',NULL,'48.00','1','OPD123454','2026-08-28 00:00:00');
INSERT INTO `medicine_purchases` VALUES ('14','TXN-20260828-053825-7448','1',NULL,'50.00','1','opd14313','2026-08-28 00:00:00');
INSERT INTO `medicine_purchases` VALUES ('15','TXN-20260828-054014-5137','2',NULL,'16.00','1','opd00003','2026-08-28 00:00:00');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medicines` VALUES ('6','Paracetamol 500mg','Paracetamol','Tablet','2.50','Active','2026-08-28 11:19:12');
INSERT INTO `medicines` VALUES ('7','Amoxicillin 500mg','Amoxicillin','Capsule','8.50','Active','2026-08-28 11:19:12');
INSERT INTO `medicines` VALUES ('8','Ibuprofen 400mg','Ibuprofen','Tablet','5.00','Active','2026-08-28 11:19:12');

DROP TABLE IF EXISTS `patients`;
CREATE TABLE `patients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_code` varchar(30) NOT NULL,
  `philhealth_id` varchar(30) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `middle_name` varchar(80) DEFAULT NULL,
  `last_name` varchar(80) NOT NULL,
  `birthdate` date NOT NULL,
  `age` int(10) unsigned DEFAULT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_code` (`patient_code`),
  KEY `idx_philhealth` (`philhealth_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `patients` VALUES ('1','1234567890','0987654321','EUSEBIO','DELIMA','LADRICA','1976-05-29',NULL,'Male','QUEZON CTY','09174446583','Active','2026-08-25 13:58:10');
INSERT INTO `patients` VALUES ('2','1122334455','1122334455','MARICEL','','VILLOCILLO','1976-01-21',NULL,'Female','SAN JOSE DEL MONTE','123456','Active','2026-08-25 14:04:00');
INSERT INTO `patients` VALUES ('3','123456','123456','Romabelle','Aguilar','Peralta','1987-01-23',NULL,'Female','Quezon City','09174446583','Active','2026-08-25 15:01:48');

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

INSERT INTO `users` VALUES ('1','Brian','$2y$12$A2396c2feORckMAwO/W8luzBoZXLXzkoQWkfQDtQ46QZeFhn9l9Di','System Administrator','Admin','Active','2026-08-25 13:30:47');
INSERT INTO `users` VALUES ('2','Aj','$2y$10$JG84UqG1gYC4Jnqgs8DEP.f0iJ0Sn0rU93VlX.GCP.dvpSok1.UXO','AJ Ortal','Pharmacist','Active','2026-08-28 11:10:19');

SET FOREIGN_KEY_CHECKS=1;
