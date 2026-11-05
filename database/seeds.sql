-- ==============================================================================
-- e-Mabalacat: Beginner-Friendly Seed Data
-- 
-- Madaling tandaan na mga demo account para sa pag-test at pag-defense ng grupo.
-- ==============================================================================

USE `emabalacat`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `ticket_merges`;
TRUNCATE TABLE `report_upvotes`;
TRUNCATE TABLE `reports`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `barangays`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------------------------
-- 1. I-INSERT ANG 27 BARANGAYS NG MABALACAT CITY
-- ------------------------------------------------------------------------------
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

-- ------------------------------------------------------------------------------
-- 2. I-INSERT ANG MGA DEMO USERS (9 Accounts para sa bawat Role)
-- ------------------------------------------------------------------------------
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `agency_type`, `barangay_id`, `kyc_status`, `unit_callsign`, `rank_title`) VALUES
-- 1. CDRRMO City Command Center (Super Admin) -> Password: Admin123!
(1, 'Ramon Manalo', 'admin@mabalacat.gov.ph', '(045) 900-0300', '$2y$12$1Z7A8HWXQ3lez/jmdvYUoeRsNaIZ1D4jIQzMcGZrCyxYssd/.gziG', 'super_admin', NULL, NULL, 'verified', 'COMMAND-1', 'CDRRMO City Administrator'),

-- 2. BFP Fire Marshal (Agency Admin) -> Password: Bfp1234!
(2, 'Capt. Renato Dizon', 'bfp.admin@mabalacat.gov.ph', '(045) 900-0911', '$2y$12$OmtqbNsxxJdj6WBW9WawMeh5gFlAOZb4xwrFsqA4.T9Z6uAw2gpSu', 'agency_admin', 'bfp', NULL, 'verified', 'BFP-CHIEF', 'BFP Fire Marshal'),

-- 3. PNP Police Chief (Agency Admin) -> Password: Pnp1234!
(3, 'PLtCol. Marlon Vega', 'pnp.admin@mabalacat.gov.ph', '(045) 900-0117', '$2y$12$02qBpN8Q8c3C7rn1f271PO3u7KEbkVtYebnQcduTQbQ7M.u/2hsfu', 'agency_admin', 'pnp', NULL, 'verified', 'PNP-HQ', 'Chief of Police'),

-- 4. Medical EMS Director (Agency Admin) -> Password: Med1234!
(4, 'Dr. Angela Cruz', 'medical.admin@mabalacat.gov.ph', '(045) 900-0912', '$2y$12$NmZ.S00gbDHkTTVfecHjTeTNPo2IozOoos.VPg4CQdE7gB3ClxESS', 'agency_admin', 'medical', NULL, 'verified', 'MED-DIR', 'City Health Officer'),

-- 5. Barangay Dau Desk (Barangay Mini-Admin) -> Password: demo1234
(5, 'Elena Dizon', 'desk@dau.mabalacat.gov.ph', '(045) 900-0208', '$2y$12$z4mhvu9ejOtZNLCWOHREEerTMni1u4iap0I31L8hbhGDcbcucgb76', 'mini_admin', NULL, 8, 'verified', 'DESK-DAU', 'Punong Barangay & Desk Officer'),

-- 6. BFP Firefighter (Responder) -> Password: Resp1234!
(6, 'FO2 Mark Mendoza', 'mendoza.bfp@gov.ph', '+63 917 334 1122', '$2y$12$Uc8i.8vRSjDHoG2VWdhyiuDajn7LTtRKVNDJfjjQm0w72DlBHcCD.', 'responder', 'bfp', NULL, 'verified', 'RED-1', 'Lead Nozzleman • Engine 1'),

-- 7. PNP Patrol Officer (Responder) -> Password: Resp1234!
(7, 'Pat. Danilo Santos', 'santos.pnp@gov.ph', '+63 918 554 8899', '$2y$12$Uc8i.8vRSjDHoG2VWdhyiuDajn7LTtRKVNDJfjjQm0w72DlBHcCD.', 'responder', 'pnp', NULL, 'verified', 'UNIT-PNP-01', 'Mobile Patrol Commander'),

-- 8. Paramedic Ambulance Crew (Responder) -> Password: Resp1234!
(8, 'EMS Nurse Carlo David', 'david.ems@gov.ph', '+63 921 776 2233', '$2y$12$Uc8i.8vRSjDHoG2VWdhyiuDajn7LTtRKVNDJfjjQm0w72DlBHcCD.', 'responder', 'medical', NULL, 'verified', 'MED-AMB-03', 'Lead Paramedic'),

-- 9. Residente ng Barangay Dau (Citizen) -> Password: password123
(9, 'Maria Santos', 'maria.santos@example.ph', '+63 917 000 1042', '$2y$12$F8xp93B/MTN6VJbHmAj5a.bvIXAJ2n3Xu2NlzaCsOtGOxlfJOGCHS', 'citizen', NULL, 8, 'verified', NULL, 'Verified Resident'),

-- 10. Bagong Residente (Pending KYC) -> Password: password123
(10, 'Aileen Mercado', 'aileen.mercado@example.ph', '+63 920 000 3381', '$2y$12$F8xp93B/MTN6VJbHmAj5a.bvIXAJ2n3Xu2NlzaCsOtGOxlfJOGCHS', 'citizen', NULL, 8, 'pending', NULL, 'Pending KYC Resident');

-- ------------------------------------------------------------------------------
-- 3. I-INSERT ANG MGA SAMPLE EMERGENCY INCIDENTS & REPORTS
-- ------------------------------------------------------------------------------
INSERT INTO `reports` (`id`, `ticket_no`, `user_id`, `barangay_id`, `target_portal`, `category`, `priority`, `description`, `address`, `latitude`, `longitude`, `fuzzed_lat`, `fuzzed_lng`, `upvotes_count`, `status`, `assigned_responder_id`, `dispatched_at`, `arrived_at`, `official_ref_no`) VALUES
-- 1. BFP Call: Active Structure Fire sa Dau
(1, 'MBL-2026-0447', 9, 8, 'bfp', 'Fire & Rescue', 'emergency',
 'May sunog sa kusina ng isang commercial building tabi ng gasolinahan. Makapal ang itim na usok.',
 'MacArthur Highway near Caltex, Barangay Dau', 15.182400, 120.589200, 15.182740, 120.589520, 28, 'Dispatched', 6, '2026-11-05 18:45:00', NULL, 'BFP-MBL-2026-0447'),

-- 2. PNP Call: Kaguluhan / Physical Disturbance sa Dau
(2, 'MBL-2026-0443', 9, 8, 'pnp', 'Public Disturbance', 'high',
 'May dalawang grupong nag-aaway sa harap ng 24/7 convenience store at may dalang mga pamalo.',
 'MacArthur Highway in front of Dau Bus Terminal', 15.178200, 120.589100, 15.178550, 120.589410, 24, 'On-Scene', 7, '2026-11-05 18:30:00', '2026-11-05 18:38:00', 'PNP-MBL-2026-0812'),

-- 3. Medical Call: Aksidente sa Daan
(3, 'MBL-2026-0440', 9, 12, 'medical', 'Vehicular Trauma', 'emergency',
 'Bungguan ng motor at tricycle. May dalawang sugatan na kailangan agad ng ambulansya.',
 'MacArthur Highway corner Mabiga Road, Mabiga', 15.246500, 120.552100, 15.246820, 120.552430, 19, 'Dispatched', 8, '2026-11-05 19:10:00', NULL, 'MDH-EMS-2026-091'),

-- 4. Barangay Call: Bumabahang Baradong Kanal sa Dau (Pending - First-to-Accept)
(4, 'MBL-2026-0452', 9, 8, 'barangay', 'Drainage & Flooding', 'emergency',
 'Mabilis na tumataas ang tubig malapit sa day-care center dahil sa baradong kanal.',
 'Purok 2, Sampaguita Street, Dau', 15.181600, 120.588150, 15.181920, 120.588420, 18, 'Pending', NULL, NULL, NULL, NULL),

-- 5. Waiting Confirmation: Naayos na Baradong Grate (May Litrato ng Proof)
(5, 'MBL-2026-0436', 9, 8, 'barangay', 'Road Maintenance', 'high',
 'Baradong drainage grate na nilinis na ng barangay crew. Awaiting citizen confirmation.',
 'Kalaklan Road, near Dau Public Market', 15.179872, 120.589949, 15.180210, 120.590210, 14, 'Waiting Confirmation', 5, '2026-11-05 14:00:00', '2026-11-05 14:25:00', 'ENG-MBL-2026-0436'),

-- 6. Fully Resolved: Nalinis na Tapon ng Basura
(6, 'MBL-2026-0410', 9, 8, 'barangay', 'Waste Management', 'medium',
 'Tambak ng basura sa tabi ng barangay hall driveway na nahakot na ng trak.',
 'Barangay Hall driveway, Dau', 15.181905, 120.590727, 15.182210, 120.591020, 8, 'Resolved', 5, '2026-11-04 09:00:00', '2026-11-04 09:30:00', 'CENRO-2026-1104');

-- ------------------------------------------------------------------------------
-- 4. I-INSERT ANG MGA SAMPLE WAZE-STYLE UPVOTES
-- ------------------------------------------------------------------------------
INSERT INTO `report_upvotes` (`report_id`, `user_id`, `comment`) VALUES
(1, 9, 'Nakikita ko rin ang usok mula sa Purok 4.'),
(1, 10, 'Malapit ako rito, kailangan ng bumbero.'),
(2, 9, 'Nandito kami sa terminal, nagkakagulo nga.'),
(4, 9, 'Bumabaha na rin sa kalye namin.');
