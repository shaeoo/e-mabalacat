-- ==============================================================================
-- e-Mabalacat: Beginner-Friendly Master Database (Schema + Seeds)
--
-- 1-Click Import Script para sa XAMPP phpMyAdmin o MySQL CLI.
-- 
-- 5 Simple Tables:
--   1. `barangays`      -> Listahan ng 27 Barangays ng Mabalacat
--   2. `users`          -> Lahat ng Users (Admin, BFP, PNP, Medical, Responders, Citizens)
--   3. `reports`        -> Lahat ng Sumbong at Emergency Incident Reports
--   4. `report_upvotes` -> Waze-Style +1 Upvotes ng mga Residente
--   5. `ticket_merges`  -> Talaan ng pinagsamang duplicate reports
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `emabalacat` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `emabalacat`;

-- ------------------------------------------------------------------------------
-- 1. BARANGAYS (Masterlist ng 27 Barangays)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `barangays` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `zone` ENUM('North', 'Central', 'South') NOT NULL DEFAULT 'North',
  `captain` VARCHAR(120) NOT NULL DEFAULT '',
  `hotline` VARCHAR(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. USERS (Isang Centralized Table para sa Lahat ng Roles)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(40) NOT NULL DEFAULT '',
  `password_hash` VARCHAR(255) NOT NULL,
  
  -- Role ng User: Super Admin, Agency Admin, Barangay Mini-Admin, Field Responder, o Citizen
  `role` ENUM('super_admin', 'agency_admin', 'mini_admin', 'responder', 'citizen') NOT NULL DEFAULT 'citizen',
  
  -- Ahensya (kung agency_admin o responder: bfp, pnp, o medical)
  `agency_type` ENUM('bfp', 'pnp', 'medical') DEFAULT NULL,
  
  -- Barangay ID (kung residente o barangay admin)
  `barangay_id` INT UNSIGNED DEFAULT NULL,
  
  -- KYC Status ng Residente (ID Verification)
  `kyc_status` ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
  `kyc_id_image` VARCHAR(255) DEFAULT NULL,
  
  -- Katayuan sa Trabaho ng Responder
  `is_on_duty` TINYINT(1) NOT NULL DEFAULT 1,
  `duty_status` ENUM('available', 'dispatched', 'en_route', 'on_site', 'off_duty') NOT NULL DEFAULT 'available',
  `unit_callsign` VARCHAR(50) DEFAULT NULL,
  `rank_title` VARCHAR(100) DEFAULT NULL,
  
  -- False Alarm Strikes & Verification
  `strike_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 1,
  `otp_code` VARCHAR(10) DEFAULT NULL,
  `otp_expires_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`barangay_id`) REFERENCES `barangays`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. REPORTS (Lahat ng Sumbong at Emergency Incident Calls)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reports` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ticket_no` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `barangay_id` INT UNSIGNED NOT NULL,
  
  -- Saan naka-route ang tawag: barangay, bfp, pnp, o medical
  `target_portal` ENUM('barangay', 'bfp', 'pnp', 'medical') NOT NULL DEFAULT 'barangay',
  
  `category` VARCHAR(100) NOT NULL,
  `priority` ENUM('emergency', 'high', 'medium', 'low') NOT NULL DEFAULT 'medium',
  `description` TEXT NOT NULL,
  `address` VARCHAR(255) NOT NULL DEFAULT '',
  `evidence_photo` VARCHAR(255) DEFAULT NULL,
  
  -- GPS Coordinates (Tunay na lokasyon para sa Responders)
  `latitude` DECIMAL(10, 7) NOT NULL DEFAULT 15.181000,
  `longitude` DECIMAL(10, 7) NOT NULL DEFAULT 120.588000,
  
  -- Fuzzed Coordinates (May 50m offset para protektahan ang privacy ng nagsumbong)
  `fuzzed_lat` DECIMAL(10, 7) NOT NULL DEFAULT 15.181000,
  `fuzzed_lng` DECIMAL(10, 7) NOT NULL DEFAULT 120.588000,
  
  -- Waze-Style Counter ng mga Sumuportang Kapitbahay
  `upvotes_count` INT UNSIGNED NOT NULL DEFAULT 1,
  
  -- Status ng Report Lifecycle
  `status` ENUM('Pending', 'Dispatched', 'On-Scene', 'Waiting Confirmation', 'Resolved', 'Closed', 'False Alarm') NOT NULL DEFAULT 'Pending',
  
  -- First-to-Accept Dispatch (Sino ang tumanggap na Responder)
  `assigned_responder_id` INT UNSIGNED DEFAULT NULL,
  `dispatched_at` DATETIME DEFAULT NULL,
  `arrived_at` DATETIME DEFAULT NULL,
  
  -- Proof of Resolution (Litrato at Blotter # pagtapos ng aksyon)
  `resolution_photo` VARCHAR(255) DEFAULT NULL,
  `resolution_remarks` TEXT DEFAULT NULL,
  `official_ref_no` VARCHAR(100) DEFAULT NULL,
  `resolved_uploaded_at` DATETIME DEFAULT NULL,
  `closed_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`barangay_id`) REFERENCES `barangays`(`id`),
  FOREIGN KEY (`assigned_responder_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. REPORT_UPVOTES (Waze-Style +1 Upvotes ng mga Residente)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `report_upvotes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `additional_photo` VARCHAR(255) DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE KEY `uq_report_user_vote` (`report_id`, `user_id`),
  FOREIGN KEY (`report_id`) REFERENCES `reports`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. TICKET_MERGES (Talaan kung may pinagsamang duplicate reports)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_merges` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `primary_report_id` INT UNSIGNED NOT NULL,
  `secondary_report_id` INT UNSIGNED NOT NULL,
  `merged_by_user_id` INT UNSIGNED NOT NULL,
  `reason` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`primary_report_id`) REFERENCES `reports`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`secondary_report_id`) REFERENCES `reports`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`merged_by_user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- DEMO SEED DATA (Malinis at Madaling I-memorize para sa Defense)
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `ticket_merges`;
TRUNCATE TABLE `report_upvotes`;
TRUNCATE TABLE `reports`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `barangays`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. 27 Barangays
INSERT INTO `barangays` (`id`, `name`, `zone`, `captain`, `hotline`) VALUES
(1, 'Atlu-Bola', 'North', 'Dante Rivera', '(045) 900-0001'),
(2, 'Bical', 'North', 'Marilou Castro', '(045) 900-0002'),
(3, 'Bundagul', 'North', 'Ferdinand Laxamana', '(045) 900-0003'),
(4, 'Cacutud', 'North', 'Rosalie Bautista', '(045) 900-0004'),
(5, 'Calumpang', 'North', 'Armando Garcia', '(045) 900-0005'),
(6, 'Camachiles', 'South', 'Cristina Fernandez', '(045) 900-0006'),
(7, 'Dapdap', 'South', 'Jose Pangan', '(045) 900-0007'),
(8, 'Dau', 'South', 'Elena Dizon', '(045) 900-0008'),
(9, 'Dolores', 'South', 'Rogelio Santos', '(045) 900-0009'),
(10, 'Duquit', 'South', 'Teresa Ocampo', '(045) 900-0010'),
(11, 'Lakandula', 'Central', 'Lito Manalo', '(045) 900-0011'),
(12, 'Mabiga', 'North', 'Celia Mendoza', '(045) 900-0012'),
(13, 'Macapagal Village', 'South', 'Nestor David', '(045) 900-0013'),
(14, 'Mamatitang', 'North', 'Marianne Aquino', '(045) 900-0014'),
(15, 'Mangalit', 'North', 'Rex Pamintuan', '(045) 900-0015'),
(16, 'Marcos Village', 'North', 'Lorna Reyes', '(045) 900-0016'),
(17, 'Mawaque', 'North', 'Alvin Dizon', '(045) 900-0017'),
(18, 'Paralimunan', 'North', 'Beth Labrador', '(045) 900-0018'),
(19, 'Poblacion', 'Central', 'Mario Pangilinan', '(045) 900-0019'),
(20, 'San Francisco', 'Central', 'Ruby Mercado', '(045) 900-0020'),
(21, 'San Joaquin', 'Central', 'Samuel Quiambao', '(045) 900-0021'),
(22, 'Santa Ines', 'Central', 'Grace Nuqui', '(045) 900-0022'),
(23, 'Santa Maria', 'North', 'Eddie Tolentino', '(045) 900-0023'),
(24, 'Santo Rosario', 'Central', 'Aida Lim', '(045) 900-0024'),
(25, 'Sapang Balen', 'South', 'Randy Sison', '(045) 900-0025'),
(26, 'Sapang Biabas', 'South', 'Divina Paras', '(045) 900-0026'),
(27, 'Tabun', 'South', 'Felix Bituin', '(045) 900-0027');

-- 2. Demo Users (1 Account para sa bawat Role)
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `agency_type`, `barangay_id`, `kyc_status`, `unit_callsign`, `rank_title`) VALUES
(1, 'Ramon Manalo', 'admin@mabalacat.gov.ph', '(045) 900-0300', '$2y$12$1Z7A8HWXQ3lez/jmdvYUoeRsNaIZ1D4jIQzMcGZrCyxYssd/.gziG', 'super_admin', NULL, NULL, 'verified', 'COMMAND-1', 'CDRRMO City Administrator'),
(2, 'Capt. Renato Dizon', 'bfp.admin@mabalacat.gov.ph', '(045) 900-0911', '$2y$12$OmtqbNsxxJdj6WBW9WawMeh5gFlAOZb4xwrFsqA4.T9Z6uAw2gpSu', 'agency_admin', 'bfp', NULL, 'verified', 'BFP-CHIEF', 'BFP Fire Marshal'),
(3, 'PLtCol. Marlon Vega', 'pnp.admin@mabalacat.gov.ph', '(045) 900-0117', '$2y$12$02qBpN8Q8c3C7rn1f271PO3u7KEbkVtYebnQcduTQbQ7M.u/2hsfu', 'agency_admin', 'pnp', NULL, 'verified', 'PNP-HQ', 'Chief of Police'),
(4, 'Dr. Angela Cruz', 'medical.admin@mabalacat.gov.ph', '(045) 900-0912', '$2y$12$NmZ.S00gbDHkTTVfecHjTeTNPo2IozOoos.VPg4CQdE7gB3ClxESS', 'agency_admin', 'medical', NULL, 'verified', 'MED-DIR', 'City Health Officer'),
(5, 'Elena Dizon', 'desk@dau.mabalacat.gov.ph', '(045) 900-0208', '$2y$12$z4mhvu9ejOtZNLCWOHREEerTMni1u4iap0I31L8hbhGDcbcucgb76', 'mini_admin', NULL, 8, 'verified', 'DESK-DAU', 'Punong Barangay & Desk Officer'),
(6, 'FO2 Mark Mendoza', 'mendoza.bfp@gov.ph', '+63 917 334 1122', '$2y$12$Uc8i.8vRSjDHoG2VWdhyiuDajn7LTtRKVNDJfjjQm0w72DlBHcCD.', 'responder', 'bfp', NULL, 'verified', 'RED-1', 'Lead Nozzleman • Engine 1'),
(7, 'Pat. Danilo Santos', 'santos.pnp@gov.ph', '+63 918 554 8899', '$2y$12$Uc8i.8vRSjDHoG2VWdhyiuDajn7LTtRKVNDJfjjQm0w72DlBHcCD.', 'responder', 'pnp', NULL, 'verified', 'UNIT-PNP-01', 'Mobile Patrol Commander'),
(8, 'EMS Nurse Carlo David', 'david.ems@gov.ph', '+63 921 776 2233', '$2y$12$Uc8i.8vRSjDHoG2VWdhyiuDajn7LTtRKVNDJfjjQm0w72DlBHcCD.', 'responder', 'medical', NULL, 'verified', 'MED-AMB-03', 'Lead Paramedic'),
(9, 'Maria Santos', 'maria.santos@example.ph', '+63 917 000 1042', '$2y$12$F8xp93B/MTN6VJbHmAj5a.bvIXAJ2n3Xu2NlzaCsOtGOxlfJOGCHS', 'citizen', NULL, 8, 'verified', NULL, 'Verified Resident'),
(10, 'Aileen Mercado', 'aileen.mercado@example.ph', '+63 920 000 3381', '$2y$12$F8xp93B/MTN6VJbHmAj5a.bvIXAJ2n3Xu2NlzaCsOtGOxlfJOGCHS', 'citizen', NULL, 8, 'pending', NULL, 'Pending KYC Resident');

-- 3. Sample Emergency Incident Reports
INSERT INTO `reports` (`id`, `ticket_no`, `user_id`, `barangay_id`, `target_portal`, `category`, `priority`, `description`, `address`, `latitude`, `longitude`, `fuzzed_lat`, `fuzzed_lng`, `upvotes_count`, `status`, `assigned_responder_id`, `dispatched_at`, `arrived_at`, `official_ref_no`) VALUES
(1, 'MBL-2026-0447', 9, 8, 'bfp', 'Fire & Rescue', 'emergency',
 'May sunog sa kusina ng isang commercial building tabi ng gasolinahan. Makapal ang itim na usok.',
 'MacArthur Highway near Caltex, Barangay Dau', 15.182400, 120.589200, 15.182740, 120.589520, 28, 'Dispatched', 6, '2026-11-05 18:45:00', NULL, 'BFP-MBL-2026-0447'),

(2, 'MBL-2026-0443', 9, 8, 'pnp', 'Public Disturbance', 'high',
 'May dalawang grupong nag-aaway sa harap ng 24/7 convenience store at may dalang mga pamalo.',
 'MacArthur Highway in front of Dau Bus Terminal', 15.178200, 120.589100, 15.178550, 120.589410, 24, 'On-Scene', 7, '2026-11-05 18:30:00', '2026-11-05 18:38:00', 'PNP-MBL-2026-0812'),

(3, 'MBL-2026-0440', 9, 12, 'medical', 'Vehicular Trauma', 'emergency',
 'Bungguan ng motor at tricycle. May dalawang sugatan na kailangan agad ng ambulansya.',
 'MacArthur Highway corner Mabiga Road, Mabiga', 15.246500, 120.552100, 15.246820, 120.552430, 19, 'Dispatched', 8, '2026-11-05 19:10:00', NULL, 'MDH-EMS-2026-091'),

(4, 'MBL-2026-0452', 9, 8, 'barangay', 'Drainage & Flooding', 'emergency',
 'Mabilis na tumataas ang tubig malapit sa day-care center dahil sa baradong kanal.',
 'Purok 2, Sampaguita Street, Dau', 15.181600, 120.588150, 15.181920, 120.588420, 18, 'Pending', NULL, NULL, NULL, NULL),

(5, 'MBL-2026-0436', 9, 8, 'barangay', 'Road Maintenance', 'high',
 'Baradong drainage grate na nilinis na ng barangay crew. Awaiting citizen confirmation.',
 'Kalaklan Road, near Dau Public Market', 15.179872, 120.589949, 15.180210, 120.590210, 14, 'Waiting Confirmation', 5, '2026-11-05 14:00:00', '2026-11-05 14:25:00', 'ENG-MBL-2026-0436'),

(6, 'MBL-2026-0410', 9, 8, 'barangay', 'Waste Management', 'medium',
 'Tambak ng basura sa tabi ng barangay hall driveway na nahakot na ng trak.',
 'Barangay Hall driveway, Dau', 15.181905, 120.590727, 15.182210, 120.591020, 8, 'Resolved', 5, '2026-11-04 09:00:00', '2026-11-04 09:30:00', 'CENRO-2026-1104');

-- 4. Sample Waze-Style Upvotes
INSERT INTO `report_upvotes` (`report_id`, `user_id`, `comment`) VALUES
(1, 9, 'Nakikita ko rin ang usok mula sa Purok 4.'),
(1, 10, 'Malapit ako rito, kailangan ng bumbero.'),
(2, 9, 'Nandito kami sa terminal, nagkakagulo nga.'),
(4, 9, 'Bumabaha na rin sa kalye namin.');
  `priority` ENUM('emergency', 'high', 'medium', 'low') NOT NULL DEFAULT 'medium',
  `description` TEXT NOT NULL,
  `address` VARCHAR(255) NOT NULL DEFAULT '',
  `evidence_photo` VARCHAR(255) DEFAULT NULL,
  
  -- GPS Coordinates (Tunay na lokasyon para sa Responders)
  `latitude` DECIMAL(10, 7) NOT NULL DEFAULT 15.181000,
  `longitude` DECIMAL(10, 7) NOT NULL DEFAULT 120.588000,
  
  -- Fuzzed Coordinates (May 50m offset para protektahan ang privacy ng nagsumbong)
  `fuzzed_lat` DECIMAL(10, 7) NOT NULL DEFAULT 15.181000,
  `fuzzed_lng` DECIMAL(10, 7) NOT NULL DEFAULT 120.588000,
  
  -- Waze-Style Counter ng mga Sumuportang Kapitbahay
  `upvotes_count` INT UNSIGNED NOT NULL DEFAULT 1,
  
  -- Status ng Report Lifecycle
  `status` ENUM('Pending', 'Dispatched', 'On-Scene', 'Waiting Confirmation', 'Resolved', 'Closed', 'False Alarm') NOT NULL DEFAULT 'Pending',
  
  -- First-to-Accept Dispatch (Sino ang tumanggap na Responder)
  `assigned_responder_id` INT UNSIGNED DEFAULT NULL,
  `dispatched_at` DATETIME DEFAULT NULL,
  `arrived_at` DATETIME DEFAULT NULL,
  
  -- Proof of Resolution (Litrato at Blotter # pagtapos ng aksyon)
  `resolution_photo` VARCHAR(255) DEFAULT NULL,
  `resolution_remarks` TEXT DEFAULT NULL,
  `official_ref_no` VARCHAR(100) DEFAULT NULL,
  `resolved_uploaded_at` DATETIME DEFAULT NULL,
  `closed_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`barangay_id`) REFERENCES `barangays`(`id`),
  FOREIGN KEY (`assigned_responder_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. REPORT_UPVOTES (Waze-Style +1 Upvotes ng mga Residente)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `report_upvotes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `additional_photo` VARCHAR(255) DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE KEY `uq_report_user_vote` (`report_id`, `user_id`),
  FOREIGN KEY (`report_id`) REFERENCES `reports`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. TICKET_MERGES (Talaan kung may pinagsamang duplicate reports)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_merges` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `primary_report_id` INT UNSIGNED NOT NULL,
  `secondary_report_id` INT UNSIGNED NOT NULL,
  `merged_by_user_id` INT UNSIGNED NOT NULL,
  `reason` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`primary_report_id`) REFERENCES `reports`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`secondary_report_id`) REFERENCES `reports`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`merged_by_user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- DEMO SEED DATA (Malinis at Madaling I-memorize para sa Defense)
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `ticket_merges`;
TRUNCATE TABLE `report_upvotes`;
TRUNCATE TABLE `reports`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `barangays`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. 27 Barangays
INSERT INTO `barangays` (`id`, `name`, `zone`, `captain`, `hotline`) VALUES
(1, 'Atlu-Bola', 'North', 'Dante Rivera', '(045) 900-0001'),
(2, 'Bical', 'North', 'Marilou Castro', '(045) 900-0002'),
(3, 'Bundagul', 'North', 'Ferdinand Laxamana', '(045) 900-0003'),
(4, 'Cacutud', 'North', 'Rosalie Bautista', '(045) 900-0004'),
(5, 'Calumpang', 'North', 'Armando Garcia', '(045) 900-0005'),
(6, 'Camachiles', 'South', 'Cristina Fernandez', '(045) 900-0006'),
(7, 'Dapdap', 'South', 'Jose Pangan', '(045) 900-0007'),
(8, 'Dau', 'South', 'Elena Dizon', '(045) 900-0008'),
(9, 'Dolores', 'South', 'Rogelio Santos', '(045) 900-0009'),
(10, 'Duquit', 'South', 'Teresa Ocampo', '(045) 900-0010'),
(11, 'Lakandula', 'Central', 'Lito Manalo', '(045) 900-0011'),
(12, 'Mabiga', 'North', 'Celia Mendoza', '(045) 900-0012'),
(13, 'Macapagal Village', 'South', 'Nestor David', '(045) 900-0013'),
(14, 'Mamatitang', 'North', 'Marianne Aquino', '(045) 900-0014'),
(15, 'Mangalit', 'North', 'Rex Pamintuan', '(045) 900-0015'),
(16, 'Marcos Village', 'North', 'Lorna Reyes', '(045) 900-0016'),
(17, 'Mawaque', 'North', 'Alvin Dizon', '(045) 900-0017'),
(18, 'Paralimunan', 'North', 'Beth Labrador', '(045) 900-0018'),
(19, 'Poblacion', 'Central', 'Mario Pangilinan', '(045) 900-0019'),
(20, 'San Francisco', 'Central', 'Ruby Mercado', '(045) 900-0020'),
(21, 'San Joaquin', 'Central', 'Samuel Quiambao', '(045) 900-0021'),
(22, 'Santa Ines', 'Central', 'Grace Nuqui', '(045) 900-0022'),
(23, 'Santa Maria', 'North', 'Eddie Tolentino', '(045) 900-0023'),
(24, 'Santo Rosario', 'Central', 'Aida Lim', '(045) 900-0024'),
(25, 'Sapang Balen', 'South', 'Randy Sison', '(045) 900-0025'),
(26, 'Sapang Biabas', 'South', 'Divina Paras', '(045) 900-0026'),
(27, 'Tabun', 'South', 'Felix Bituin', '(045) 900-0027');

-- 2. Demo Users (1 Account para sa bawat Role)
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `agency_type`, `barangay_id`, `kyc_status`, `unit_callsign`, `rank_title`) VALUES
(1, 'Ramon Manalo', 'admin@mabalacat.gov.ph', '(045) 900-0300', '$2y$12$1Z7A8HWXQ3lez/jmdvYUoeRsNaIZ1D4jIQzMcGZrCyxYssd/.gziG', 'super_admin', NULL, NULL, 'verified', 'COMMAND-1', 'CDRRMO City Administrator'),
(2, 'Capt. Renato Dizon', 'bfp.admin@mabalacat.gov.ph', '(045) 900-0911', '$2y$12$OmtqbNsxxJdj6WBW9WawMeh5gFlAOZb4xwrFsqA4.T9Z6uAw2gpSu', 'agency_admin', 'bfp', NULL, 'verified', 'BFP-CHIEF', 'BFP Fire Marshal'),
(3, 'PLtCol. Marlon Vega', 'pnp.admin@mabalacat.gov.ph', '(045) 900-0117', '$2y$12$02qBpN8Q8c3C7rn1f271PO3u7KEbkVtYebnQcduTQbQ7M.u/2hsfu', 'agency_admin', 'pnp', NULL, 'verified', 'PNP-HQ', 'Chief of Police'),
(4, 'Dr. Angela Cruz', 'medical.admin@mabalacat.gov.ph', '(045) 900-0912', '$2y$12$NmZ.S00gbDHkTTVfecHjTeTNPo2IozOoos.VPg4CQdE7gB3ClxESS', 'agency_admin', 'medical', NULL, 'verified', 'MED-DIR', 'City Health Officer'),
(5, 'Elena Dizon', 'desk@dau.mabalacat.gov.ph', '(045) 900-0208', '$2y$12$z4mhvu9ejOtZNLCWOHREEerTMni1u4iap0I31L8hbhGDcbcucgb76', 'mini_admin', NULL, 8, 'verified', 'DESK-DAU', 'Punong Barangay & Desk Officer'),
(6, 'FO2 Mark Mendoza', 'mendoza.bfp@gov.ph', '+63 917 334 1122', '$2y$12$Uc8i.8vRSjDHoG2VWdhyiuDajn7LTtRKVNDJfjjQm0w72DlBHcCD.', 'responder', 'bfp', NULL, 'verified', 'RED-1', 'Lead Nozzleman • Engine 1'),
(7, 'Pat. Danilo Santos', 'santos.pnp@gov.ph', '+63 918 554 8899', '$2y$12$Uc8i.8vRSjDHoG2VWdhyiuDajn7LTtRKVNDJfjjQm0w72DlBHcCD.', 'responder', 'pnp', NULL, 'verified', 'UNIT-PNP-01', 'Mobile Patrol Commander'),
(8, 'EMS Nurse Carlo David', 'david.ems@gov.ph', '+63 921 776 2233', '$2y$12$Uc8i.8vRSjDHoG2VWdhyiuDajn7LTtRKVNDJfjjQm0w72DlBHcCD.', 'responder', 'medical', NULL, 'verified', 'MED-AMB-03', 'Lead Paramedic'),
(9, 'Maria Santos', 'maria.santos@example.ph', '+63 917 000 1042', '$2y$12$F8xp93B/MTN6VJbHmAj5a.bvIXAJ2n3Xu2NlzaCsOtGOxlfJOGCHS', 'citizen', NULL, 8, 'verified', NULL, 'Verified Resident'),
(10, 'Aileen Mercado', 'aileen.mercado@example.ph', '+63 920 000 3381', '$2y$12$F8xp93B/MTN6VJbHmAj5a.bvIXAJ2n3Xu2NlzaCsOtGOxlfJOGCHS', 'citizen', NULL, 8, 'pending', NULL, 'Pending KYC Resident');

-- 3. Sample Emergency Incident Reports
INSERT INTO `reports` (`id`, `ticket_no`, `user_id`, `barangay_id`, `target_portal`, `category`, `priority`, `description`, `address`, `latitude`, `longitude`, `fuzzed_lat`, `fuzzed_lng`, `upvotes_count`, `status`, `assigned_responder_id`, `dispatched_at`, `arrived_at`, `official_ref_no`) VALUES
(1, 'MBL-2026-0447', 9, 8, 'bfp', 'Fire & Rescue', 'emergency',
 'May sunog sa kusina ng isang commercial building tabi ng gasolinahan. Makapal ang itim na usok.',
 'MacArthur Highway near Caltex, Barangay Dau', 15.182400, 120.589200, 15.182740, 120.589520, 28, 'Dispatched', 6, '2026-11-05 18:45:00', NULL, 'BFP-MBL-2026-0447'),

(2, 'MBL-2026-0443', 9, 8, 'pnp', 'Public Disturbance', 'high',
 'May dalawang grupong nag-aaway sa harap ng 24/7 convenience store at may dalang mga pamalo.',
 'MacArthur Highway in front of Dau Bus Terminal', 15.178200, 120.589100, 15.178550, 120.589410, 24, 'On-Scene', 7, '2026-11-05 18:30:00', '2026-11-05 18:38:00', 'PNP-MBL-2026-0812'),

(3, 'MBL-2026-0440', 9, 12, 'medical', 'Vehicular Trauma', 'emergency',
 'Bungguan ng motor at tricycle. May dalawang sugatan na kailangan agad ng ambulansya.',
 'MacArthur Highway corner Mabiga Road, Mabiga', 15.246500, 120.552100, 15.246820, 120.552430, 19, 'Dispatched', 8, '2026-11-05 19:10:00', NULL, 'MDH-EMS-2026-091'),

(4, 'MBL-2026-0452', 9, 8, 'barangay', 'Drainage & Flooding', 'emergency',
 'Mabilis na tumataas ang tubig malapit sa day-care center dahil sa baradong kanal.',
 'Purok 2, Sampaguita Street, Dau', 15.181600, 120.588150, 15.181920, 120.588420, 18, 'Pending', NULL, NULL, NULL, NULL),

(5, 'MBL-2026-0436', 9, 8, 'barangay', 'Road Maintenance', 'high',
 'Baradong drainage grate na nilinis na ng barangay crew. Awaiting citizen confirmation.',
 'Kalaklan Road, near Dau Public Market', 15.179872, 120.589949, 15.180210, 120.590210, 14, 'Waiting Confirmation', 5, '2026-11-05 14:00:00', '2026-11-05 14:25:00', 'ENG-MBL-2026-0436'),

(6, 'MBL-2026-0410', 9, 8, 'barangay', 'Waste Management', 'medium',
 'Tambak ng basura sa tabi ng barangay hall driveway na nahakot na ng trak.',
 'Barangay Hall driveway, Dau', 15.181905, 120.590727, 15.182210, 120.591020, 8, 'Resolved', 5, '2026-11-04 09:00:00', '2026-11-04 09:30:00', 'CENRO-2026-1104');

-- 4. Sample Waze-Style Upvotes
INSERT INTO `report_upvotes` (`report_id`, `user_id`, `comment`) VALUES
(1, 9, 'Nakikita ko rin ang usok mula sa Purok 4.'),
(1, 10, 'Malapit ako rito, kailangan ng bumbero.'),
(2, 9, 'Nandito kami sa terminal, nagkakagulo nga.'),
(4, 9, 'Bumabaha na rin sa kalye namin.');
