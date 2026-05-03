fundacion-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:         8.4.3 - MySQL Community Server - GPL
-- Server OS:              Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for foundation
CREATE DATABASE IF NOT EXISTS `foundation` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `foundation`;

-- Dumping structure for table foundation.audit_log
CREATE TABLE IF NOT EXISTS `audit_log` (
  `audit_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entity` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` enum('insert','update','delete') COLLATE utf8mb4_unicode_ci NOT NULL,
  `actor_id` int unsigned DEFAULT NULL,
  `changes` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  KEY `idx_audit_entity` (`entity`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.audit_log: ~0 rows (approx)

-- Dumping structure for table foundation.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.cache: ~2 rows (approx)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel-cache-livewire-rate-limiter:056fc329aaaa757d31db450f525da23fde4d1b36', 'i:1;', 1764099383),
	('laravel-cache-livewire-rate-limiter:056fc329aaaa757d31db450f525da23fde4d1b36:timer', 'i:1764099383;', 1764099383);

-- Dumping structure for table foundation.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.cache_locks: ~0 rows (approx)

-- Dumping structure for table foundation.campaigns
CREATE TABLE IF NOT EXISTS `campaigns` (
  `campaign_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_type_id` int unsigned NOT NULL,
  `monetary_goal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `campaign_status` enum('draft','active','finished','hidden') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`campaign_id`),
  UNIQUE KEY `uq_campaign_slug` (`slug`),
  KEY `idx_campaign_status` (`campaign_status`),
  KEY `fk_campaign_currency` (`currency_type_id`),
  CONSTRAINT `fk_campaign_currency` FOREIGN KEY (`currency_type_id`) REFERENCES `currency_types` (`currency_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.campaigns: ~0 rows (approx)

-- Dumping structure for table foundation.certificates
CREATE TABLE IF NOT EXISTS `certificates` (
  `certificate_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `donation_id` bigint unsigned NOT NULL,
  `folio` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pdf_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`certificate_id`),
  UNIQUE KEY `uq_cert_folio` (`folio`),
  UNIQUE KEY `uq_cert_donation` (`donation_id`),
  CONSTRAINT `fk_cert_donation` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`donation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.certificates: ~24 rows (approx)
INSERT INTO `certificates` (`certificate_id`, `donation_id`, `folio`, `pdf_url`, `issued_on`) VALUES
	(1, 1, 'c0b80ac9-62f8-4ab7-a9b1-51f355bf26fc', 'certificados/donacion-c0b80ac9-62f8-4ab7-a9b1-51f355bf26fc.pdf', '2025-11-12 15:10:43'),
	(2, 2, '21e819ac-d6d4-4756-9129-42dd637a3fca', 'certificados/donacion-21e819ac-d6d4-4756-9129-42dd637a3fca.pdf', '2025-11-12 16:18:22'),
	(3, 3, 'cabbe2e2-c0ad-11f0-9097-30e37a1a6bf6', 'certificados/prueba-1.pdf', '2025-11-13 16:28:31'),
	(4, 4, 'cabbfdb2-c0ad-11f0-9097-30e37a1a6bf6', 'certificados/prueba-2.pdf', '2025-11-04 16:28:31'),
	(5, 5, 'cabbff69-c0ad-11f0-9097-30e37a1a6bf6', 'certificados/prueba-3.pdf', '2025-10-15 16:28:31'),
	(6, 6, 'cabc0018-c0ad-11f0-9097-30e37a1a6bf6', 'certificados/prueba-4.pdf', '2025-11-09 16:28:31'),
	(7, 7, 'cabc00a8-c0ad-11f0-9097-30e37a1a6bf6', 'certificados/prueba-5.pdf', '2025-10-25 16:28:31'),
	(8, 8, '33145b4d-4190-4386-9ea6-346b4cdfca17', 'certificados/donacion-33145b4d-4190-4386-9ea6-346b4cdfca17.pdf', '2025-11-13 22:33:57'),
	(9, 9, '80f17cf6-53b5-4b2a-b480-7cf5a1fa2832', 'certificados/donacion-80f17cf6-53b5-4b2a-b480-7cf5a1fa2832.pdf', '2025-11-13 23:30:59'),
	(10, 10, '4892abee-0b3b-4d4f-b7e8-ef442e1e8bf9', 'certificados/donacion-4892abee-0b3b-4d4f-b7e8-ef442e1e8bf9.pdf', '2025-11-13 23:30:59'),
	(11, 11, 'e16771a7-551b-4cc6-8b0a-a44dad4ff7c7', 'certificados/donacion-e16771a7-551b-4cc6-8b0a-a44dad4ff7c7.pdf', '2025-11-13 23:30:59'),
	(12, 12, '21c5162c-3773-412b-8c3d-143008b8b385', 'certificados/donacion-21c5162c-3773-412b-8c3d-143008b8b385.pdf', '2025-11-13 23:30:59'),
	(13, 13, '32f8a4ed-14f4-4008-8a7e-3778ef9a6491', 'certificados/donacion-32f8a4ed-14f4-4008-8a7e-3778ef9a6491.pdf', '2025-11-13 23:30:59'),
	(14, 14, '962ee6b5-330c-4c8c-9615-092154e0acb5', 'certificados/donacion-962ee6b5-330c-4c8c-9615-092154e0acb5.pdf', '2025-11-13 23:40:29'),
	(15, 15, 'af59b27a-499c-4df2-a3f4-acf29f30afd7', 'certificados/donacion-af59b27a-499c-4df2-a3f4-acf29f30afd7.pdf', '2025-11-13 23:40:29'),
	(16, 16, '2b641e1d-6c1a-4278-976b-b0a2d42c34c3', 'certificados/donacion-2b641e1d-6c1a-4278-976b-b0a2d42c34c3.pdf', '2025-11-13 23:40:29'),
	(17, 17, '9ace0c3c-2aac-42f2-8c74-8567215e6bed', 'certificados/donacion-9ace0c3c-2aac-42f2-8c74-8567215e6bed.pdf', '2025-11-13 23:40:29'),
	(18, 18, '4564f03b-244e-4183-8616-ed5ba14d91c2', 'certificados/donacion-4564f03b-244e-4183-8616-ed5ba14d91c2.pdf', '2025-11-13 23:40:29'),
	(19, 19, '30de7df6-9f3c-4126-8cc9-5dd31c9a3ffd', 'certificados/donacion-30de7df6-9f3c-4126-8cc9-5dd31c9a3ffd.pdf', '2025-11-25 23:08:20'),
	(20, 20, '4a08764e-3a85-4c4b-b811-6de3958be786', 'certificados/donacion-4a08764e-3a85-4c4b-b811-6de3958be786.pdf', '2025-11-25 23:08:21'),
	(21, 21, '941b76ef-463e-4ef9-abee-41c6df80be53', 'certificados/donacion-941b76ef-463e-4ef9-abee-41c6df80be53.pdf', '2025-11-25 23:08:21'),
	(22, 22, '93798775-c1af-4dfc-a3e4-afebcd4e9a62', 'certificados/donacion-93798775-c1af-4dfc-a3e4-afebcd4e9a62.pdf', '2025-11-25 23:08:21'),
	(23, 23, '57a10bc2-15d3-4bc7-8d60-9b5204dd5e5e', 'certificados/donacion-57a10bc2-15d3-4bc7-8d60-9b5204dd5e5e.pdf', '2025-11-25 23:08:22'),
	(24, 24, 'b9820f46-c29b-4459-9342-eace016fadf8', 'certificados/donacion-b9820f46-c29b-4459-9342-eace016fadf8.pdf', '2025-11-25 23:08:22');

-- Dumping structure for table foundation.contacts
CREATE TABLE IF NOT EXISTS `contacts` (
  `contact_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.contacts: ~0 rows (approx)

-- Dumping structure for table foundation.availabilities
CREATE TABLE IF NOT EXISTS `availabilities` (
  `availability_id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`availability_id`),
  UNIQUE KEY `uq_availability_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.availabilities: ~0 rows (approx)

-- Dumping structure for table foundation.donations
CREATE TABLE IF NOT EXISTS `donations` (
  `donation_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `campaign_id` int unsigned DEFAULT NULL,
  `donor_id` bigint unsigned DEFAULT NULL,
  `qr_id` bigint unsigned DEFAULT NULL,
  `currency_type_id` int unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','succeeded','failed','refunded','canceled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `provider` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_payment_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_subscription_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `is_anonymous` tinyint(1) NOT NULL DEFAULT '0',
  `fee` decimal(12,2) DEFAULT NULL,
  `net_amount` decimal(12,2) DEFAULT NULL,
  `channel` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`donation_id`),
  UNIQUE KEY `uq_provider_payment` (`provider`,`provider_payment_id`),
  KEY `idx_donations_campaign_status_date` (`campaign_id`,`status`,`date`),
  KEY `idx_donations_donor_date` (`donor_id`,`date`),
  KEY `fk_donation_currency` (`currency_type_id`),
  KEY `fk_donation_qr` (`qr_id`),
  CONSTRAINT `fk_donation_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`),
  CONSTRAINT `fk_donation_donor` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`donor_id`),
  CONSTRAINT `fk_donation_currency` FOREIGN KEY (`currency_type_id`) REFERENCES `currency_types` (`currency_type_id`),
  CONSTRAINT `fk_donation_qr` FOREIGN KEY (`qr_id`) REFERENCES `qrs` (`qr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.donations: ~22 rows (approx)
INSERT INTO `donations` (`donation_id`, `date`, `campaign_id`, `donor_id`, `qr_id`, `currency_type_id`, `amount`, `status`, `provider`, `provider_payment_id`, `provider_subscription_id`, `is_recurring`, `is_anonymous`, `fee`, `net_amount`, `channel`, `ip`, `user_agent`, `created_at`, `updated_at`) VALUES
	(1, '2025-11-12 19:10:36', NULL, 1, NULL, 1, 250.00, 'succeeded', 'test', 'test_6914a36c9a222', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-12 19:10:36', '2025-11-12 19:10:36'),
	(2, '2025-11-12 20:18:21', NULL, 2, NULL, 1, 300.00, 'succeeded', 'test_auth', 'auth_test_6914b34dd9cf5', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-12 20:18:21', '2025-11-12 20:18:21'),
	(3, '2025-11-12 16:28:30', NULL, 2, NULL, 1, 500.00, 'succeeded', 'SQL_TEST', 'SQL_TEST_ca9ada46-c0ad-11f0-9097-30e37a1a6bf6', NULL, 0, 0, 15.00, 485.00, NULL, NULL, NULL, '2025-11-13 16:28:30', '2025-11-13 16:28:30'),
	(4, '2025-11-03 16:28:30', NULL, 2, NULL, 1, 150.75, 'succeeded', 'SQL_TEST', 'SQL_TEST_caa2e1fa-c0ad-11f0-9097-30e37a1a6bf6', NULL, 0, 0, 10.00, 140.75, NULL, NULL, NULL, '2025-11-13 16:28:30', '2025-11-13 16:28:30'),
	(5, '2025-10-14 16:28:31', NULL, 2, NULL, 1, 1200.00, 'succeeded', 'SQL_TEST', 'SQL_TEST_caa919b0-c0ad-11f0-9097-30e37a1a6bf6', NULL, 0, 1, 50.00, 1150.00, NULL, NULL, NULL, '2025-11-13 16:28:31', '2025-11-13 16:28:31'),
	(6, '2025-11-08 16:28:31', NULL, 2, NULL, 1, 200.00, 'succeeded', 'SQL_TEST', 'SQL_TEST_caaf37d8-c0ad-11f0-9097-30e37a1a6bf6', NULL, 1, 0, 10.00, 190.00, NULL, NULL, NULL, '2025-11-13 16:28:31', '2025-11-13 16:28:31'),
	(7, '2025-10-24 16:28:31', NULL, 2, NULL, 1, 100.00, 'succeeded', 'SQL_TEST', 'SQL_TEST_cab53e44-c0ad-11f0-9097-30e37a1a6bf6', NULL, 0, 0, 5.00, 95.00, NULL, NULL, NULL, '2025-11-13 16:28:31', '2025-11-13 16:28:31'),
	(8, '2025-11-13 22:33:48', NULL, 1, NULL, 1, 250.00, 'succeeded', 'test', 'test_6916248ce4262', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 22:33:48', '2025-11-13 22:33:48'),
	(9, '2025-11-13 23:30:57', NULL, 3, NULL, 1, 351.00, 'succeeded', 'test_masivo', 'mass_691631f1717f9', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
	(10, '2025-11-13 23:30:57', NULL, 3, NULL, 1, 352.00, 'succeeded', 'test_masivo', 'mass_691631f17a67d', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
	(11, '2025-11-13 23:30:57', NULL, 3, NULL, 1, 353.00, 'succeeded', 'test_masivo', 'mass_691631f17e9f4', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
	(12, '2025-11-13 23:30:57', NULL, 3, NULL, 1, 354.00, 'succeeded', 'test_masivo', 'mass_691631f182316', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
	(13, '2025-11-13 23:30:57', NULL, 3, NULL, 1, 355.00, 'succeeded', 'test_masivo', 'mass_691631f1840cc', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
	(14, '2025-11-13 23:40:26', NULL, 2, NULL, 1, 351.00, 'succeeded', 'test_masivo', 'mass_6916342acf97e', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 23:40:26', '2025-11-13 23:40:26'),
	(15, '2025-11-13 23:40:26', NULL, 2, NULL, 1, 352.00, 'succeeded', 'test_masivo', 'mass_6916342ad8073', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 23:40:26', '2025-11-13 23:40:26'),
	(16, '2025-11-13 23:40:26', NULL, 2, NULL, 1, 353.00, 'succeeded', 'test_masivo', 'mass_6916342ad9736', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 23:40:26', '2025-11-13 23:40:26'),
	(17, '2025-11-13 23:40:26', NULL, 2, NULL, 1, 354.00, 'succeeded', 'test_masivo', 'mass_6916342adb464', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 23:40:26', '2025-11-13 23:40:26'),
	(18, '2025-11-13 23:40:26', NULL, 2, NULL, 1, 355.00, 'succeeded', 'test_masivo', 'mass_6916342add19f', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 23:40:26', '2025-11-13 23:40:26'),
	(19, '2025-11-24 22:42:28', NULL, 4, NULL, 1, 100.00, 'succeeded', 'seed_script', 'seed_6925f894e011e', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-25 22:42:28', '2025-11-25 22:42:28'),
	(20, '2025-11-23 22:42:32', NULL, 4, NULL, 1, 200.00, 'succeeded', 'seed_script', 'seed_6925f89842239', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-25 22:42:32', '2025-11-25 22:42:32'),
	(21, '2025-11-22 22:42:32', NULL, 4, NULL, 1, 300.00, 'succeeded', 'seed_script', 'seed_6925f89844bf7', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-25 22:42:32', '2025-11-25 22:42:32'),
	(22, '2025-11-24 22:57:29', NULL, 4, NULL, 1, 100.00, 'succeeded', 'seed_script', 'seed_6925fc19cf351', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-25 22:57:29', '2025-11-25 22:57:29'),
	(23, '2025-11-23 22:57:29', NULL, 4, NULL, 1, 200.00, 'succeeded', 'seed_script', 'seed_6925fc19ddfec', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-25 22:57:29', '2025-11-25 22:57:29'),
	(24, '2025-11-22 22:57:29', NULL, 4, NULL, 1, 300.00, 'succeeded', 'seed_script', 'seed_6925fc19dfdf2', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-25 22:57:29', '2025-11-25 22:57:29');

-- Dumping structure for table foundation.in_kind_donations
CREATE TABLE IF NOT EXISTS `in_kind_donations` (
  `in_kind_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `donor_id` bigint unsigned DEFAULT NULL,
  `campaign_id` int unsigned DEFAULT NULL,
  `item` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `estimated_value` decimal(12,2) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`in_kind_id`),
  KEY `idx_inkind_campaign` (`campaign_id`),
  KEY `fk_inkind_donor` (`donor_id`),
  CONSTRAINT `fk_inkind_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`),
  CONSTRAINT `fk_inkind_donor` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`donor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.in_kind_donations: ~0 rows (approx)

-- Dumping structure for table foundation.donation_subscriptions
CREATE TABLE IF NOT EXISTS `donation_subscriptions` (
  `subscription_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `donor_id` bigint unsigned NOT NULL,
  `campaign_id` int unsigned DEFAULT NULL,
  `provider` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_subscription_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `interval` enum('monthly','quarterly','annual') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency_type_id` int unsigned NOT NULL,
  `status` enum('active','paused','canceled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `start_date` date NOT NULL,
  `cancellation_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`subscription_id`),
  UNIQUE KEY `uq_subscription_provider` (`provider`,`provider_subscription_id`),
  KEY `idx_subscription_donor` (`donor_id`),
  KEY `fk_subscription_campaign` (`campaign_id`),
  KEY `fk_subscription_currency` (`currency_type_id`),
  CONSTRAINT `fk_subscription_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`),
  CONSTRAINT `fk_subscription_donor` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`donor_id`),
  CONSTRAINT `fk_subscription_currency` FOREIGN KEY (`currency_type_id`) REFERENCES `currency_types` (`currency_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.donation_subscriptions: ~0 rows (approx)

-- Dumping structure for table foundation.donors
CREATE TABLE IF NOT EXISTS `donors` (
  `donor_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int unsigned DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marketing_opt_in` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`donor_id`),
  KEY `idx_donors_email` (`email`),
  KEY `fk_donor_person` (`person_id`),
  CONSTRAINT `fk_donor_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.donors: ~3 rows (approx)
INSERT INTO `donors` (`donor_id`, `person_id`, `name`, `last_name`, `email`, `phone`, `country`, `city`, `marketing_opt_in`, `created_at`, `updated_at`) VALUES
	(1, NULL, NULL, NULL, 'favian.prueba@correo.com', '77712345', NULL, NULL, 0, '2025-11-12 14:06:25', NULL),
	(2, 3, 'Usuario', 'Panel', 'panel.test@fundacion.org', '777-DONA-TEST', NULL, NULL, 0, '2025-11-12 20:18:21', '2025-11-12 20:18:21'),
	(3, NULL, 'Donante', 'Masivo', 'donante.masivo@prueba.com', '99988877', NULL, NULL, 0, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
	(4, 4, 'Donante', 'Prueba', 'donante.prueba@fundacion.org', '77777777', NULL, NULL, 0, '2025-11-25 22:42:28', '2025-11-25 22:42:28');

-- Dumping structure for table foundation.events
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campaign_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `idx_event_campaign` (`campaign_id`),
  CONSTRAINT `fk_event_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.events: ~0 rows (approx)

-- Dumping structure for table foundation.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.failed_jobs: ~1 rows (approx)
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES (1, 'd926e656-dd48-451d-aa43-692b66760cb2', 'database', 'default', '{"uuid":"d926e656-dd48-451d-aa43-692b66760cb2","displayName":"App\\\\Jobs\\\\DiagnosticarAlmacenamientoJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\DiagnosticarAlmacenamientoJob","command":"O:46:\"App\\\\Jobs\\\\DiagnosticarAlmacenamientoJob\":1:{s:8:\"\u0000*\u0000job\";O:45:\"Illuminate\\\\Queue\\\\Jobs\\\\DatabaseJob\":10:{s:10:\"\u0000*\u0000connection\";s:8:\"database\";s:5:\"queue\";s:7:\"default\";s:8:\"\u0000*\u0000job\";O:8:\"stdClass\":12:{s:2:\"id\";i:1;s:5:\"queue\";s:7:\"default\";s:8:\"payload\";s:11891:\"{"uuid":"d926e656-dd48-451d-aa43-692b66760cb2","displayName":"App\\\\Jobs\\\\DiagnosticarAlmacenamientoJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\DiagnosticarAlmacenamientoJob","command":"O:46:\"App\\\\Jobs\\\\DiagnosticarAlmacenamientoJob\":1:{s:8:\"\u0000*\u0000job\";N;}"}}";s:12:\"attempts\";i:1;s:10:\"reserved_at\";i:1764098950;s:11:\"available_at\";i:1764097455;s:9:\"created_at\";i:1764097455;s:11:\"exception\";s:0:\"\";s:5:\"queue\";s:7:\"default\";s:8:\"reserved\";i:0;s:10:\"reserved_at\";N;s:11:\"available_at\";i:1764097720;}}";s:11:\"maxTries\";N;s:14:\"maxExceptions\";N;s:13:\"failOnTimeout\";b:0;s:6:\"delay\";i:0;s:9:\"deletedAt\";N;}}"}', 'C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(401): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#17 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(187): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#18 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#19 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#20 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#21 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#22 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#23 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#24 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#25 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#26 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(176): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#27 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\symfony\\console\\Command\\Command.php(314): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#28 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Dispatcher.php(142): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#29 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Application.php(102): Illuminate\\Console\\Dispatcher->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#30 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(171): Illuminate\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#31 C:\\Users\\favia\\Documents\\NuestraEsperanza\\NuestraEsperanza\\FundacionNuestraEsperanza-main\\laravel\\fundacion-esperanza\\artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 {main}', '2025-11-25 09:33:40');

-- Dumping structure for table foundation.media_assets
CREATE TABLE IF NOT EXISTS `media_assets` (
  `id_media` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` bigint unsigned NOT NULL DEFAULT '0',
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_media`),
  UNIQUE KEY `uq_media_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.media_assets: ~0 rows (approx)

-- Dumping structure for table foundation.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.migrations: ~5 rows (approx)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(2, '2025_10_18_063625_create_personal_access_tokens_table', 2),
	(3, '2025_11_23_143554_ajustar_tablas_frontend', 3);

-- Dumping structure for table foundation.news
CREATE TABLE IF NOT EXISTS `news` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publication_date` date NOT NULL DEFAULT '2025-11-25',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.news: ~0 rows (approx)

-- Dumping structure for table foundation.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int unsigned DEFAULT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`notification_id`),
  KEY `idx_notif_person` (`person_id`),
  CONSTRAINT `fk_notif_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.notifications: ~0 rows (approx)

-- Dumping structure for table foundation.pages
CREATE TABLE IF NOT EXISTS `pages` (
  `page_id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_on` timestamp NULL DEFAULT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `uq_pages_slug` (`slug`),
  KEY `idx_pages_status_published` (`status`,`published_on`),
  KEY `fk_pages_author` (`author_id`),
  CONSTRAINT `fk_pages_author` FOREIGN KEY (`author_id`) REFERENCES `persons` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.pages: ~0 rows (approx)

-- Dumping structure for table foundation.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.password_reset_tokens: ~0 rows (approx)

-- Dumping structure for table foundation.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.personal_access_tokens: ~19 rows (approx)
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
	(1, 'App\\Models\\Persona', 1, 'api', '59a35ca87689e762920fb718b071001a4eb0026614be3ebd5350f03efa3d9cac', '["*"]', NULL, NULL, '2025-10-18 10:38:48', '2025-10-18 10:38:48'),
	(2, 'App\\Models\\Persona', 1, 'api', 'be98041ad7d2362ea67dbf80f909c0346fa1d1c8c5c7d605c8a143b8a8b13d2f', '["*"]', '2025-10-18 10:48:32', NULL, '2025-10-18 10:46:16', '2025-10-18 10:48:32'),
	(3, 'App\\Models\\Persona', 1, 'api', '3ef2809e53524b80b2d69941a396264d8a23072c4e5114a806c9e0787e7481a5', '["*"]', NULL, NULL, '2025-10-18 10:49:15', '2025-10-18 10:49:15'),
	(4, 'App\\Models\\Persona', 1, 'api', 'f22c675c9703661be46a8553f191147a242c3660d5b768e1694f2604924c1303', '["*"]', '2025-10-19 11:38:09', NULL, '2025-10-18 10:50:35', '2025-10-19 11:38:09'),
	(5, 'App\\Models\\Persona', 1, 'api', '23d24075f782f9c464293f77341e97d1e2e920d363b904944b1c7847b7194411', '["*"]', '2025-10-19 12:44:48', NULL, '2025-10-19 12:44:03', '2025-10-19 12:44:48'),
	(6, 'App\\Models\\Persona', 1, 'api', 'a6723b7b20468a44b9e151efb3c4a24f0c406082c9f13458d341908d1323f46f', '["*"]', '2025-10-21 17:34:02', NULL, '2025-10-21 17:33:43', '2025-10-21 17:34:02'),
	(7, 'App\\Models\\Persona', 1, 'api', 'f2b6830500139e80e30d74483712613917865c328e122b5e28a9b319113fa098', '["*"]', '2025-10-21 17:40:48', NULL, '2025-10-21 17:40:48', '2025-10-21 17:40:48'),
	(8, 'App\\Models\\Persona', 1, 'api', '4b32fae7a1772186716ff9ec9c69d80d2efb4528c3080e2d19488a0b08082988', '["*"]', '2025-10-21 17:40:49', NULL, '2025-10-21 17:40:49', '2025-10-21 17:40:49'),
	(9, 'App\\Models\\Persona', 1, 'api', '1b08e7a034b7f94d9b237599015948332467d1656910382346765d70a256f081', '["*"]', '2025-10-21 17:40:49', NULL, '2025-10-21 17:40:49', '2025-10-21 17:40:49'),
	(10, 'App\\Models\\Persona', 1, 'api', '31575855f410522c069411985c490a02f6e5257522501672322a36b533e507c5', '["*"]', '2025-10-21 17:40:50', NULL, '2025-10-21 17:40:50', '2025-10-21 17:40:50'),
	(11, 'App\\Models\\Persona', 1, 'api', 'b0799af311a37c768e7d23f3366838b0222f7797745778a487a2a68c07e0c4ce', '["*"]', '2025-10-21 17:41:05', NULL, '2025-10-21 17:41:05', '2025-10-21 17:41:05'),
	(12, 'App\\Models\\Persona', 1, 'api', '4c1d488e2c7c919d675af59a5840b3c6a495b5465ef31e8436440c83a54d651a', '["*"]', '2025-11-25 15:02:12', NULL, '2025-11-25 15:02:12', '2025-11-25 15:02:12'),
	(13, 'App\\Models\\Persona', 1, 'api', 'b2b8045618451f21183206c6b84004928b9c8ce33d455447b8565a78cc314e36', '["*"]', '2025-11-25 15:05:07', NULL, '2025-11-25 15:05:07', '2025-11-25 15:05:07'),
	(14, 'App\\Models\\Persona', 1, 'api', '923984501a4e2ef3097b6a67f191b24135e5d3c8c767e7275005b81a7a2503d4', '["*"]', '2025-11-25 15:10:40', NULL, '2025-11-25 15:10:40', '2025-11-25 15:10:40'),
	(15, 'App\\Models\\Persona', 1, 'api', '6df7e0503f671c261e389e4ff50d75a646c24388e6e5a6a6230f2c417937d570', '["*"]', '2025-11-25 21:05:01', NULL, '2025-11-25 21:05:01', '2025-11-25 21:05:01'),
	(16, 'App\\Models\\Persona', 1, 'api', 'c4424e6261596e42b87f212261e47a95079a45693d258ce2459a9af5d3159955', '["*"]', '2025-11-25 23:08:26', NULL, '2025-11-25 23:08:26', '2025-11-25 23:08:26'),
	(17, 'App\\Models\\Persona', 4, 'api', '339904945d7d3d526e3c091edc38ef8704ce20af9a8501168128214fa762b322', '["*"]', '2025-11-25 23:18:24', NULL, '2025-11-25 23:18:24', '2025-11-25 23:18:24'),
	(36, 'App\\Models\\Persona', 1, 'api', 'a062af3b85d95267039a82084b6935c10696956270fa67f2e1a49f8021c2780c', '["*"]', '2025-11-25 23:23:54', NULL, '2025-11-25 23:23:52', '2025-11-25 23:23:54'),
	(37, 'App\\Models\\Persona', 1, 'api', '070e31ad31132a9022100c62c93116afbe322c7eb3c49b758366a7fef7689974', '["*"]', '2025-11-25 23:24:17', NULL, '2025-11-25 23:23:52', '2025-11-25 23:24:17');

-- Dumping structure for table foundation.persons
CREATE TABLE IF NOT EXISTS `persons` (
  `person_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paternal_last_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maternal_last_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ci` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`person_id`),
  UNIQUE KEY `uq_persons_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.persons: ~6 rows (approx)
INSERT INTO `persons` (`person_id`, `name`, `paternal_last_name`, `maternal_last_name`, `email`, `password`, `ci`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Super', 'Admin', NULL, 'admin@fundacion.org', '$2y$12$Bi6ZA1JWiTskN/Yl757.8.H356z3L9bF.Wk80gP9qPqP5P/w.yP.w', '1234567', 1, '2025-10-18 07:43:56', '2025-10-21 17:34:02'),
	(2, 'Editor', 'Test', 'CMS', 'editor@fundacion.org', '$2y$12$W9yH5H5D1xX5G0cT0kL0L.Q.7u2q8f3s0c0c0c0c0c0c0c0c0c0c', '7654321', 1, '2025-10-18 07:43:56', '2025-10-18 07:43:56'),
	(3, 'Tesorero', 'Test', 'Panel', 'panel.test@fundacion.org', '$2y$12$W9yH5H5D1xX5G0cT0kL0L.Q.7u2q8f3s0c0c0c0c0c0c0c0c0c0c', '9876543', 1, '2025-10-18 07:43:56', '2025-10-18 07:43:56'),
	(4, 'Donante', 'Prueba', NULL, 'donante.prueba@fundacion.org', '$2y$12$Bi6ZA1JWiTskN/Yl757.8.H356z3L9bF.Wk80gP9qPqP5P/w.yP.w', '11111111', 1, '2025-11-25 22:42:28', '2025-11-25 22:42:28'),
	(5, 'User', 'Deleted', NULL, 'user.deleted@fundacion.org', '$2y$12$Bi6ZA1JWiTskN/Yl757.8.H356z3L9bF.Wk80gP9qPqP5P/w.yP.w', '22222222', 0, '2025-11-25 22:42:28', '2025-11-25 22:42:28'),
	(6, 'Otro', 'Usuario', NULL, 'otro.usuario@fundacion.org', '$2y$12$Bi6ZA1JWiTskN/Yl757.8.H356z3L9bF.Wk80gP9qPqP5P/w.yP.w', '33333333', 1, '2025-11-25 22:42:28', '2025-11-25 22:42:28');

-- Dumping structure for table foundation.person_roles
CREATE TABLE IF NOT EXISTS `person_roles` (
  `person_id` int unsigned NOT NULL,
  `role_id` int unsigned NOT NULL,
  PRIMARY KEY (`person_id`,`role_id`),
  KEY `fk_pr_role` (`role_id`),
  CONSTRAINT `fk_pr_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`person_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pr_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.person_roles: ~5 rows (approx)
INSERT INTO `person_roles` (`person_id`, `role_id`) VALUES
	(1, 1),
	(2, 2),
	(3, 3),
	(4, 5),
	(6, 4);

-- Dumping structure for table foundation.posts
CREATE TABLE IF NOT EXISTS `posts` (
  `post_id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_on` timestamp NULL DEFAULT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`),
  UNIQUE KEY `uq_posts_slug` (`slug`),
  KEY `idx_posts_status_published` (`status`,`published_on`),
  KEY `fk_posts_author` (`author_id`),
  CONSTRAINT `fk_posts_author` FOREIGN KEY (`author_id`) REFERENCES `persons` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.posts: ~0 rows (approx)

-- Dumping structure for table foundation.post_media
CREATE TABLE IF NOT EXISTS `post_media` (
  `post_id` int unsigned NOT NULL,
  `media_id` int unsigned NOT NULL,
  `position` int unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`post_id`,`media_id`),
  KEY `fk_pm_media` (`media_id`),
  CONSTRAINT `fk_pm_media` FOREIGN KEY (`media_id`) REFERENCES `media_assets` (`id_media`) ON DELETE CASCADE,
  CONSTRAINT `fk_pm_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.post_media: ~0 rows (approx)

-- Dumping structure for table foundation.post_tags
CREATE TABLE IF NOT EXISTS `post_tags` (
  `post_id` int unsigned NOT NULL,
  `tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `fk_pt_tag` (`tag_id`),
  CONSTRAINT `fk_pt_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pt_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.post_tags: ~0 rows (approx)

-- Dumping structure for table foundation.qrs
CREATE TABLE IF NOT EXISTS `qrs` (
  `qr_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiration_date` timestamp NULL DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campaign_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`qr_id`),
  UNIQUE KEY `uq_qr_code` (`code`),
  KEY `idx_qr_campaign` (`campaign_id`),
  CONSTRAINT `fk_qr_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.qrs: ~0 rows (approx)

-- Dumping structure for table foundation.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `uq_roles_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.roles: ~5 rows (approx)
INSERT INTO `roles` (`role_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'admin', 'Acceso total', '2025-10-18 07:43:57', '2025-10-18 07:43:57'),
	(2, 'editor', 'CMS y campaÃ±as', '2025-10-18 07:43:58', '2025-10-18 07:43:58'),
	(3, 'tesorero', 'Finanzas y donaciones', '2025-10-18 07:43:58', '2025-10-18 07:43:58'),
	(4, 'viewer', 'Solo lectura', '2025-10-18 07:43:58', '2025-10-18 07:43:58'),
	(5, 'donante', 'Usuario con capacidad de ver historial de donaciones y descargar certificados.', '2025-11-12 20:18:21', '2025-11-12 20:18:21');

-- Dumping structure for table foundation.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.sessions: ~2 rows (approx)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('6hW9oQyDq2cE6R2O5i9r5i7s2i2D1g4C', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 OPR/123.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNHQwcVBFd0k5Yld5ZzFySGRZS2VVYWs1YXhqMkZtejMwOERvd09DTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvYXV0aC9kb25hY2lvbmVzL21pcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764097753),
	('QAUs4lss7I6fywbcsxjzTaNxemxcvPa8We05kzeu', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibElNT1NUVUphY05udzFkTDZXNDcya3VuenFhclBvdWs1ZHhHSXZDdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvYXV0aC9kb25hY2lvbmVzL21pcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764097461);

-- Dumping structure for table foundation.tags
CREATE TABLE IF NOT EXISTS `tags` (
  `tag_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `uq_tags_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.tags: ~0 rows (approx)

-- Dumping structure for table foundation.testimonials
CREATE TABLE IF NOT EXISTS `testimonials` (
  `testimonial_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`testimonial_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.testimonials: ~0 rows (approx)

-- Dumping structure for table foundation.currency_types
CREATE TABLE IF NOT EXISTS `currency_types` (
  `currency_type_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso_code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`currency_type_id`),
  UNIQUE KEY `uq_ct_code` (`iso_code`),
  UNIQUE KEY `uq_ct_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.currency_types: ~1 rows (approx)
INSERT INTO `currency_types` (`currency_type_id`, `name`, `iso_code`, `symbol`) VALUES
	(1, 'DÃ³lar Estadounidense', 'USD', '$');

-- Dumping structure for table foundation.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.users: ~0 rows (approx)

-- Dumping structure for table foundation.videos
CREATE TABLE IF NOT EXISTS `videos` (
  `video_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.videos: ~0 rows (approx)

-- Dumping structure for table foundation.video_testimonials
CREATE TABLE IF NOT EXISTS `video_testimonials` (
  `vt_id` int unsigned NOT NULL AUTO_INCREMENT,
  `testimonial_id` int unsigned DEFAULT NULL,
  `video_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`vt_id`),
  KEY `idx_vt_testimonial` (`testimonial_id`),
  KEY `idx_vt_video` (`video_id`),
  CONSTRAINT `fk_vt_testimonial` FOREIGN KEY (`testimonial_id`) REFERENCES `testimonials` (`testimonial_id`),
  CONSTRAINT `fk_vt_video` FOREIGN KEY (`video_id`) REFERENCES `videos` (`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.video_testimonials: ~0 rows (approx)

-- Dumping structure for table foundation.volunteers
CREATE TABLE IF NOT EXISTS `volunteers` (
  `volunteer_id` int unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int unsigned DEFAULT NULL,
  `incorporation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `availability_id` tinyint unsigned DEFAULT NULL,
  `skills` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`volunteer_id`),
  KEY `idx_volunteer_person` (`person_id`),
  KEY `idx_volunteer_availability` (`availability_id`),
  CONSTRAINT `fk_volunteer_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`person_id`),
  CONSTRAINT `fk_volunteer_availability` FOREIGN KEY (`availability_id`) REFERENCES `availabilities` (`availability_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table foundation.volunteers: ~0 rows (approx)

-- Dumping structure for view foundation.vw_complete_donations
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vw_complete_donations` (
  `donation_id` BIGINT UNSIGNED NOT NULL,
  `date` TIMESTAMP NOT NULL,
  `campaign` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
  `donor` VARCHAR(201) NULL COLLATE 'utf8mb4_unicode_ci',
  `email` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending','succeeded','failed','refunded','canceled') NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `provider` VARCHAR(40) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `is_recurring` TINYINT(1) NOT NULL,
  `is_anonymous` TINYINT(1) NOT NULL,
  `currency_symbol` VARCHAR(10) NULL COLLATE 'utf8mb4_unicode_ci',
  `currency_name` VARCHAR(50) NULL COLLATE 'utf8mb4_unicode_ci',
  `fee` DECIMAL(12,2) NULL,
  `net_amount` DECIMAL(12,2) NULL,
  `certifiable` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Dumping structure for view foundation.vw_daily_summary
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vw_daily_summary` (
  `date` DATE NULL,
  `count` BIGINT NOT NULL,
  `successful_amount` DECIMAL(34,2) NULL,
  `failed_count` DECIMAL(23,0) NULL
) ENGINE=MyISAM;

-- Dumping structure for view foundation.vw_active_subscriptions
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vw_active_subscriptions` (
  `subscription_id` BIGINT UNSIGNED NOT NULL,
  `donor_id` BIGINT UNSIGNED NOT NULL,
  `campaign_id` INT UNSIGNED NULL,
  `provider` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `provider_subscription_id` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `interval` ENUM('monthly','quarterly','annual') NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `amount` DECIMAL(12,2) NOT NULL,
  `currency_type_id` INT UNSIGNED NOT NULL,
  `status` ENUM('active','paused','canceled') NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `start_date` DATE NOT NULL,
  `cancellation_date` DATE NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `email` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `campaign` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Dumping structure for view foundation.vw_complete_volunteers
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vw_complete_volunteers` (
  `volunteer_id` INT UNSIGNED NOT NULL,
  `person_id` INT UNSIGNED NULL,
  `name` VARCHAR(101) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `email` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `incorporation_date` TIMESTAMP NOT NULL,
  `availability` VARCHAR(60) NULL COLLATE 'utf8mb4_unicode_ci',
  `skills` VARCHAR(255) NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Dropping temporary table and creating final VIEW structure
DROP TABLE IF EXISTS `vw_complete_donations`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_complete_donations` AS select `d`.`donation_id` AS `donation_id`, `d`.`date` AS `date`, `c`.`name` AS `campaign`, (case when (`d`.`is_anonymous` = 1) then 'AnÃ³nimo' else concat_ws(' ',`dn`.`name`,`dn`.`last_name`) end) AS `donor`, `dn`.`email` AS `email`, `d`.`amount` AS `amount`, `d`.`status` AS `status`, `d`.`provider` AS `provider`, `d`.`is_recurring` AS `is_recurring`, `d`.`is_anonymous` AS `is_anonymous`, `tm`.`symbol` AS `currency_symbol`, `tm`.`name` AS `currency_name`, `d`.`fee` AS `fee`, `d`.`net_amount` AS `net_amount`, (case when ((`d`.`status` = 'succeeded') and (`d`.`is_anonymous` = 0)) then 'si' else 'no' end) AS `certifiable` from (((`donations` `d` left join `donors` `dn` on((`dn`.`donor_id` = `d`.`donor_id`))) left join `campaigns` `c` on((`c`.`campaign_id` = `d`.`campaign_id`))) left join `currency_types` `tm` on((`tm`.`currency_type_id` = `d`.`currency_type_id`)));

-- Dropping temporary table and creating final VIEW structure
DROP TABLE IF EXISTS `vw_daily_summary`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_daily_summary` AS select cast(`d`.`date` as date) AS `date`,count(0) AS `count`,sum((case when (`d`.`status` = 'succeeded') then `d`.`amount` else 0 end)) AS `successful_amount`,sum((case when (`d`.`status` = 'failed') then 1 else 0 end)) AS `failed_count` from `donations` `d` group by cast(`d`.`date` as date) order by cast(`d`.`date` as date) desc;

-- Dropping temporary table and creating final VIEW structure
DROP TABLE IF EXISTS `vw_active_subscriptions`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_active_subscriptions` AS select `s`.`subscription_id` AS `subscription_id`,`s`.`donor_id` AS `donor_id`,`s`.`campaign_id` AS `campaign_id`,`s`.`provider` AS `provider`,`s`.`provider_subscription_id` AS `provider_subscription_id`,`s`.`interval` AS `interval`,`s`.`amount` AS `amount`,`s`.`currency_type_id` AS `currency_type_id`,`s`.`status` AS `status`,`s`.`start_date` AS `start_date`,`s`.`cancellation_date` AS `cancellation_date`,`s`.`created_at` AS `created_at`,`s`.`updated_at` AS `updated_at`,`dn`.`email` AS `email`,`c`.`name` AS `campaign` from ((`donation_subscriptions` `s` left join `donors` `dn` on((`dn`.`donor_id` = `s`.`donor_id`))) left join `campaigns` `c` on((`c`.`campaign_id` = `s`.`campaign_id`)));

-- Dropping temporary table and creating final VIEW structure
DROP TABLE IF EXISTS `vw_complete_volunteers`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_complete_volunteers` AS select `v`.`volunteer_id` AS `volunteer_id`,`p`.`person_id` AS `person_id`,concat_ws(' ',`p`.`name`,`p`.`paternal_last_name`) AS `name`,`p`.`email` AS `email`,`v`.`incorporation_date` AS `incorporation_date`,`d`.`label` AS `availability`,`v`.`skills` AS `skills` from ((`volunteers` `v` left join `persons` `p` on((`p`.`person_id` = `v`.`person_id`))) left join `availabilities` `d` on((`d`.`availability_id` = `v`.`availability_id`)));

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;