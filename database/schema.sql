-- ==============================================================================
-- e-Mabalacat: Beginner-Friendly Master Database Schema (v2.0)
-- 
-- 5 Simple at Malinaw na Tables Lamang:
--   1. `barangays`      -> Listahan ng 27 Barangays sa Mabalacat City
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
