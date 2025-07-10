-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: SvwfeoXW
-- ------------------------------------------------------
-- Server version	8.4.3

USE SvwfeoXW;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'user_login','2024-02-15 08:00:00'),(2,2,'story_created','2024-01-20 14:15:00'),(3,2,'story_created','2024-02-10 16:20:00'),(4,3,'story_created','2024-01-15 10:30:00'),(5,3,'comment_posted','2024-01-21 16:15:00'),(6,4,'story_created','2024-01-25 12:00:00'),(7,5,'comment_posted','2024-01-17 14:20:00'),(8,6,'story_created','2024-02-05 11:30:00'),(9,6,'comment_posted','2024-01-18 11:45:00'),(10,7,'story_created','2024-02-08 13:20:00'),(11,7,'comment_posted','2024-02-03 15:45:00');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_category` enum('auth','user','security','system','data') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_id` int unsigned DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_url` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `severity` enum('info','warning','error','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `additional_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_event_category` (`event_category`),
  KEY `idx_resource` (`resource_type`,`resource_id`),
  KEY `idx_severity` (`severity`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_events` (`user_id`,`event_type`,`created_at`),
  KEY `idx_security_events` (`event_category`,`severity`,`created_at`),
  CONSTRAINT `fk_audit_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `story_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `story_id` (`story_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,1,2,NULL,'Thank you for sharing your story. It really helps to know that others have gone through similar experiences.','approved','2024-01-16 09:30:00','2024-01-16 09:30:00'),(2,1,5,NULL,'This is such an inspiring story. Your positive attitude is remarkable.','approved','2024-01-17 14:20:00','2024-01-17 14:20:00'),(3,1,6,NULL,'I was just diagnosed last month. Your story gives me hope. Thank you.','approved','2024-01-18 11:45:00','2024-01-18 11:45:00'),(4,2,3,NULL,'This is exactly the kind of information I needed when I started dialysis. Great advice!','approved','2024-01-21 16:15:00','2024-01-21 16:15:00'),(5,2,4,NULL,'As a healthcare worker, I appreciate how well you\'ve explained the patient perspective.','approved','2024-01-22 10:30:00','2024-01-22 10:30:00'),(6,3,1,NULL,'What a beautiful tribute to your donor. Thank you for sharing this journey.','approved','2024-02-02 13:20:00','2024-02-02 13:20:00'),(7,3,7,NULL,'I\'m on the transplant waiting list. This gives me so much hope.','approved','2024-02-03 15:45:00','2024-02-03 15:45:00'),(8,3,2,NULL,'Six months post-transplant is amazing! Wishing you continued health.','approved','2024-02-04 09:10:00','2024-02-04 09:10:00'),(9,5,6,NULL,'Ďakujem za zdieľanie vášho príbehu. Je povzbudzujúce čítať o úspešnom zvládnutí tejto diagnózy.','approved','2024-01-26 12:30:00','2024-01-26 12:30:00'),(10,5,4,NULL,'Váš pozitívny prístup je inšpiráciou pre všetkých nás.','approved','2024-01-27 14:15:00','2024-01-27 14:15:00'),(11,7,7,NULL,'Muchas gracias por compartir tu experiencia. Me ayuda mucho como alguien que acaba de empezar diálisis.','approved','2024-01-31 11:20:00','2024-01-31 11:20:00'),(12,7,3,NULL,'Tu historia es muy valiosa. Gracias por tomarte el tiempo de escribirla.','approved','2024-02-01 16:40:00','2024-02-01 16:40:00'),(13,8,3,NULL,'Qué historia tan inspiradora. Tu fuerza es admirable.','approved','2024-02-09 10:30:00','2024-02-09 10:30:00'),(14,8,7,NULL,'Necesitaba leer esto hoy. Gracias por recordarme que soy más fuerte de lo que pienso.','approved','2024-02-10 13:45:00','2024-02-10 13:45:00');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `database_migrations`
--

DROP TABLE IF EXISTS `database_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `database_migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int unsigned NOT NULL,
  `executed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_migration` (`migration`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_migrations`
--

LOCK TABLES `database_migrations` WRITE;
/*!40000 ALTER TABLE `database_migrations` DISABLE KEYS */;
INSERT INTO `database_migrations` VALUES (1,'001_create_normalized_schema',1,'2025-07-09 16:20:35');
/*!40000 ALTER TABLE `database_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_verifications`
--

DROP TABLE IF EXISTS `email_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_verifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_token` (`token`),
  CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_verifications`
--

LOCK TABLES `email_verifications` WRITE;
/*!40000 ALTER TABLE `email_verifications` DISABLE KEYS */;
INSERT INTO `email_verifications` VALUES (1,8,'8d54789343ccb46463a49ed7a8a26b50','2025-07-04 22:12:50');
/*!40000 ALTER TABLE `email_verifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_verifications_new`
--

DROP TABLE IF EXISTS `email_verifications_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_verifications_new` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `verified_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `verification_type` enum('registration','email_change','login_verification') COLLATE utf8mb4_unicode_ci DEFAULT 'registration',
  `old_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token_hash` (`token_hash`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_email` (`email`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_is_verified` (`is_verified`),
  KEY `idx_verification_type` (`verification_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_cleanup` (`expires_at`,`is_verified`),
  CONSTRAINT `fk_email_verifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_verifications_new`
--

LOCK TABLES `email_verifications_new` WRITE;
/*!40000 ALTER TABLE `email_verifications_new` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_verifications_new` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_login_attempts`
--

DROP TABLE IF EXISTS `failed_login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_login_attempts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `username_or_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failure_reason` enum('invalid_credentials','account_suspended','account_inactive','too_many_attempts','user_not_found','email_not_verified','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempted_password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'POST',
  `request_url` text COLLATE utf8mb4_unicode_ci,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempt_count` int unsigned DEFAULT '1',
  `is_blocked` tinyint(1) DEFAULT '0',
  `blocked_until` timestamp NULL DEFAULT NULL,
  `threat_level` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `additional_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_username_or_email` (`username_or_email`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_failure_reason` (`failure_reason`),
  KEY `idx_is_blocked` (`is_blocked`),
  KEY `idx_blocked_until` (`blocked_until`),
  KEY `idx_threat_level` (`threat_level`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_ip_attempts` (`ip_address`,`created_at`),
  KEY `idx_user_attempts` (`user_id`,`created_at`),
  KEY `idx_active_blocks` (`is_blocked`,`blocked_until`),
  KEY `idx_threat_analysis` (`threat_level`,`failure_reason`,`created_at`),
  CONSTRAINT `failed_login_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_login_attempts`
--

LOCK TABLES `failed_login_attempts` WRITE;
/*!40000 ALTER TABLE `failed_login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `language_preferences`
--

DROP TABLE IF EXISTS `language_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `language_preferences` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `language_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `proficiency_level` enum('beginner','intermediate','advanced','native') COLLATE utf8mb4_unicode_ci DEFAULT 'intermediate',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_language` (`user_id`,`language_code`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_language_code` (`language_code`),
  KEY `idx_is_primary` (`is_primary`),
  CONSTRAINT `fk_language_preferences_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `language_preferences`
--

LOCK TABLES `language_preferences` WRITE;
/*!40000 ALTER TABLE `language_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `language_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `success` tinyint(1) DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_identifier` (`identifier`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_logs`
--

DROP TABLE IF EXISTS `login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'web',
  `additional_data` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_username` (`username`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_login_time` (`login_time`),
  KEY `idx_login_source` (`login_source`),
  CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_logs`
--

LOCK TABLES `login_logs` WRITE;
/*!40000 ALTER TABLE `login_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logout_logs`
--

DROP TABLE IF EXISTS `logout_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logout_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logout_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logout_source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'web',
  `additional_data` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_username` (`username`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_logout_time` (`logout_time`),
  KEY `idx_logout_source` (`logout_source`),
  CONSTRAINT `logout_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logout_logs`
--

LOCK TABLES `logout_logs` WRITE;
/*!40000 ALTER TABLE `logout_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logout_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets_new`
--

DROP TABLE IF EXISTS `password_resets_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets_new` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `used_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token_hash` (`token_hash`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_is_used` (`is_used`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_cleanup` (`expires_at`,`is_used`),
  CONSTRAINT `fk_password_resets_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets_new`
--

LOCK TABLES `password_resets_new` WRITE;
/*!40000 ALTER TABLE `password_resets_new` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets_new` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_limits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_identifier` (`identifier`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=461 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_limits`
--

LOCK TABLES `rate_limits` WRITE;
/*!40000 ALTER TABLE `rate_limits` DISABLE KEYS */;
INSERT INTO `rate_limits` VALUES (436,'127.0.0.1',1751704405),(437,'127.0.0.1',1751704406),(438,'127.0.0.1',1751704407),(439,'127.0.0.1',1751704407),(440,'127.0.0.1',1751704407),(441,'127.0.0.1',1751704408),(442,'127.0.0.1',1751704408),(443,'127.0.0.1',1751704408),(444,'127.0.0.1',1751704409),(445,'127.0.0.1',1751704409),(446,'127.0.0.1',1751704409),(447,'127.0.0.1',1751704409),(448,'127.0.0.1',1751704410),(449,'127.0.0.1',1751704410),(450,'127.0.0.1',1751704410),(451,'127.0.0.1',1751704411),(452,'127.0.0.1',1751704411),(453,'127.0.0.1',1751704411),(454,'127.0.0.1',1751704411),(455,'127.0.0.1',1751704412),(456,'127.0.0.1',1751704412),(457,'127.0.0.1',1751704412),(458,'127.0.0.1',1751704412),(459,'127.0.0.1',1751704413),(460,'127.0.0.1',1751704413);
/*!40000 ALTER TABLE `rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_events`
--

DROP TABLE IF EXISTS `security_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `event_type` enum('login_success','login_failure','logout','password_change','email_change','account_locked','account_unlocked','suspicious_activity') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `risk_score` tinyint unsigned DEFAULT '0',
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempt_count` int unsigned DEFAULT '1',
  `blocked_until` timestamp NULL DEFAULT NULL,
  `additional_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_risk_score` (`risk_score`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_blocked_until` (`blocked_until`),
  KEY `idx_user_events` (`user_id`,`event_type`,`created_at`),
  KEY `idx_security_analysis` (`event_type`,`risk_score`,`created_at`),
  CONSTRAINT `fk_security_events_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_events`
--

LOCK TABLES `security_events` WRITE;
/*!40000 ALTER TABLE `security_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_logs`
--

DROP TABLE IF EXISTS `security_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event` (`event`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_logs`
--

LOCK TABLES `security_logs` WRITE;
/*!40000 ALTER TABLE `security_logs` DISABLE KEYS */;
INSERT INTO `security_logs` VALUES (1,'user_login_success','{\"user_id\": 1, \"username\": \"admin\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64)','2024-02-15 08:00:00'),(2,'user_login_success','{\"user_id\": 2, \"username\": \"john_doe\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64)','2024-02-15 09:15:00'),(3,'user_login_success','{\"user_id\": 3, \"username\": \"maria_gonzalez\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64)','2024-02-15 10:30:00'),(4,'password_reset_request','{\"email\": \"test@example.com\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64)','2024-02-14 16:20:00');
/*!40000 ALTER TABLE `security_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stories`
--

DROP TABLE IF EXISTS `stories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `category_id` int unsigned NOT NULL,
  `original_language` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `status` enum('draft','pending_review','published','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `access_level` enum('public','registered','verified','premium') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `published_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_access_level` (`access_level`),
  KEY `idx_published_at` (`published_at`),
  CONSTRAINT `stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `story_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stories`
--

LOCK TABLES `stories` WRITE;
/*!40000 ALTER TABLE `stories` DISABLE KEYS */;
INSERT INTO `stories` VALUES (1,3,1,'en','published','public','2024-01-15 10:30:00','2024-01-15 11:00:00','2024-01-15 11:00:00'),(2,2,2,'en','published','registered','2024-01-20 14:15:00','2024-01-20 15:00:00','2024-01-20 15:00:00'),(3,6,4,'en','published','public','2024-02-01 09:45:00','2024-02-01 10:30:00','2024-02-01 10:30:00'),(4,2,5,'en','draft','public','2024-02-10 16:20:00','2024-02-10 16:20:00',NULL),(5,4,1,'sk','published','public','2024-01-25 12:00:00','2024-01-25 13:00:00','2024-01-25 13:00:00'),(6,6,3,'sk','published','verified','2024-02-05 11:30:00','2024-02-05 12:15:00','2024-02-05 12:15:00'),(7,3,2,'es','published','public','2024-01-30 15:45:00','2024-01-30 16:30:00','2024-01-30 16:30:00'),(8,7,4,'es','published','public','2024-02-08 13:20:00','2024-02-08 14:00:00','2024-02-08 14:00:00'),(9,2,2,'en','published','public','2025-07-04 20:16:59','2025-07-04 22:16:59','2025-07-04 22:16:59');
/*!40000 ALTER TABLE `stories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `story_categories`
--

DROP TABLE IF EXISTS `story_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `story_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `story_categories`
--

LOCK TABLES `story_categories` WRITE;
/*!40000 ALTER TABLE `story_categories` DISABLE KEYS */;
INSERT INTO `story_categories` VALUES (1,'General','general','General stories about kidney health and experiences','2025-07-04 18:51:52','2025-07-04 18:51:52'),(2,'Dialysis','dialysis','Stories related to dialysis treatment and experiences','2025-07-04 18:51:52','2025-07-04 18:51:52'),(3,'Pre-Transplant','pre-transplant','Experiences before kidney transplantation','2025-07-04 18:51:52','2025-07-04 18:51:52'),(4,'Post-Transplant','post-transplant','Life after kidney transplantation','2025-07-04 18:51:52','2025-07-04 18:51:52'),(5,'Lifestyle','lifestyle','Living with kidney disease - lifestyle adaptations','2025-07-04 18:51:52','2025-07-04 18:51:52'),(6,'Nutrition','nutrition','Diet and nutrition for kidney health','2025-07-04 18:51:52','2025-07-04 18:51:52'),(7,'Mental Health','mental-health','Mental and emotional aspects of kidney disease','2025-07-04 18:51:52','2025-07-04 18:51:52'),(8,'Success Stories','success-stories','Inspiring success stories from the community','2025-07-04 18:51:52','2025-07-04 18:51:52');
/*!40000 ALTER TABLE `story_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `story_contents`
--

DROP TABLE IF EXISTS `story_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `story_contents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `story_id` int unsigned NOT NULL,
  `language` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `translator_id` int unsigned DEFAULT NULL,
  `status` enum('draft','pending_review','published','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_story_language` (`story_id`,`language`),
  KEY `translator_id` (`translator_id`),
  KEY `idx_language` (`language`),
  KEY `idx_status` (`status`),
  CONSTRAINT `story_contents_ibfk_1` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `story_contents_ibfk_2` FOREIGN KEY (`translator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `story_contents`
--

LOCK TABLES `story_contents` WRITE;
/*!40000 ALTER TABLE `story_contents` DISABLE KEYS */;
INSERT INTO `story_contents` VALUES (1,1,'en','My Journey with Kidney Disease','<p>When I was first diagnosed with chronic kidney disease, I felt like my world had turned upside down. The doctor\'s words echoed in my mind as I tried to process what this meant for my future.</p>\n<p>The initial shock gave way to a determination to learn everything I could about my condition. I researched treatment options, connected with support groups, and most importantly, refused to let this diagnosis define my limitations.</p>\n<p>Today, three years later, I want to share my story to help others who might be walking a similar path. There is hope, there is support, and there is life beyond a kidney disease diagnosis.</p>','When I was first diagnosed with chronic kidney disease, I felt like my world had turned upside down. The doctor\'s words echoed in my mind...','A personal journey through kidney disease diagnosis, treatment, and finding hope and support along the way.',NULL,'published','2024-01-15 10:30:00','2024-01-15 11:00:00'),(2,2,'en','Dialysis: What I Wish I Had Known','<p>Starting dialysis was one of the most challenging experiences of my life. The appointments, the restrictions, the fatigue – it all felt overwhelming at first.</p>\n<p>But I want to share what I\'ve learned over the past year that might help someone else prepare for this journey. First, don\'t be afraid to ask questions. Your healthcare team is there to help, and no question is too small.</p>\n<p>Second, find your support system. Whether it\'s family, friends, or fellow patients, having people who understand makes all the difference.</p>\n<p>Finally, remember that this is just one part of your story, not the end of it.</p>','Starting dialysis was one of the most challenging experiences of my life. The appointments, the restrictions, the fatigue...','Practical advice and emotional support for those beginning their dialysis journey.',NULL,'published','2024-01-20 14:15:00','2024-01-20 15:00:00'),(3,3,'en','Finding Strength After Transplant','<p>Receiving a kidney transplant was a gift I never expected. The call came at 2 AM on a Tuesday, and within hours, my life changed forever.</p>\n<p>The recovery wasn\'t easy. There were complications, setbacks, and moments of doubt. But every day, I reminded myself of the incredible generosity of my donor and their family.</p>\n<p>Now, six months post-transplant, I\'m learning to live with gratitude for every normal day. I\'m sharing my story to honor my donor and to encourage others on the transplant list to keep hope alive.</p>','Receiving a kidney transplant was a gift I never expected. The call came at 2 AM on a Tuesday...','A story of hope, recovery, and gratitude following a successful kidney transplant.',NULL,'published','2024-02-01 09:45:00','2024-02-01 10:30:00'),(4,4,'en','Supporting a Loved One Through Treatment','<p>This is a draft story about supporting my wife through her kidney disease journey. It\'s been challenging for our whole family, but we\'ve learned so much about resilience and love.</p>\n<p>I want to share our experience from the caregiver\'s perspective, including the practical and emotional challenges we\'ve faced.</p>','This is a draft story about supporting my wife through her kidney disease journey...','A caregiver\'s perspective on supporting a loved one through kidney disease treatment.',NULL,'draft','2024-02-10 16:20:00','2024-02-10 16:20:00'),(5,5,'sk','Môj príbeh s ochorením obličiek','<p>Keď mi diagnostikovali chronické ochorenie obličiek, cítil som sa, akoby sa môj svet obrátil naruby. Slová lekára mi zneli v hlave, kým som sa snažil pochopiť, co to znamená pre moju budúcnosť.</p>\n<p>Počiatočný šok ustúpil odhodlaniu naučiť sa všetko o mojom stave. Skúmal som možnosti liečby, spojil som sa so skupinami podpory a čo je najdôležitejšie, odmietol som nechať túto diagnózu definovať moje obmedzenia.</p>\n<p>Dnes, o tri roky neskôr, chcem podeliť o môj príbeh, aby som pomohol ostatným, ktorí možno kráčajú podobnou cestou.</p>','Keď mi diagnostikovali chronické ochorenie obličiek, cítil som sa, akoby sa môj svet obrátil naruby...','Osobný príbeh cesty ochorením obličiek, liečbou a hľadaním nádeje a podpory.',NULL,'published','2024-01-25 12:00:00','2024-01-25 13:00:00'),(6,6,'sk','Rodina a podpora počas liečby','<p>Najťažšou časťou môjho boja s ochorením obličiek nebolo fyzické utrpenie, ale strach z toho, ako to ovplyvní moju rodinu.</p>\n<p>Moje deti boli ešte malé, keď som sa dozvedela o svojej diagnóze. Nevedela som, ako im to vysvetliť, ako ich pripraviť na zmeny, ktoré prídu.</p>\n<p>Ale naučila som sa, že deti sú silnejšie, než si myslíme. S láskou a otvoreno komunikáciou sme to zvládli spoločne.</p>','Najťažšou časťou môjho boja s ochorením obličiek nebolo fyzické utrpenie...','Príbeh o tom, ako choroba ovplyvňuje celú rodinu a ako spoločne hľadať silu.',NULL,'published','2024-02-05 11:30:00','2024-02-05 12:15:00'),(7,7,'es','Mi experiencia con la diálisis','<p>Comenzar la diálisis fue uno de los momentos más difíciles de mi vida. Al principio, todo parecía abrumador: las citas constantes, las restricciones dietéticas, el cansancio constante.</p>\n<p>Pero quiero compartir lo que he aprendido durante este primer año que podría ayudar a alguien más a prepararse para este viaje. Primero, no tengas miedo de hacer preguntas. Tu equipo médico está ahí para ayudarte.</p>\n<p>Segundo, encuentra tu sistema de apoyo. Ya sea familia, amigos o compañeros pacientes, tener personas que entiendan hace toda la diferencia.</p>','Comenzar la diálisis fue uno de los momentos más difíciles de mi vida. Al principio, todo parecía abrumador...','Experiencia personal con la diálisis y consejos para otros pacientes.',NULL,'published','2024-01-30 15:45:00','2024-01-30 16:30:00'),(8,8,'es','Superando los obstáculos','<p>Mi camino hacia la recuperación no ha sido fácil. Ha habido días oscuros, momentos de desesperanza, y veces cuando quería rendirme.</p>\n<p>Pero también ha habido momentos de luz, de esperanza, de pequeñas victorias que me han dado fuerzas para continuar.</p>\n<p>Hoy quiero compartir cómo he aprendido a encontrar la fuerza en los momentos más difíciles, porque creo que todos necesitamos recordar que somos más fuertes de lo que pensamos.</p>','Mi camino hacia la recuperación no ha sido fácil. Ha habido días oscuros, momentos de desesperanza...','Una historia de superación personal y búsqueda de la fuerza interior.',NULL,'published','2024-02-08 13:20:00','2024-02-08 14:00:00'),(9,9,'en','My Kidney Journey - A Test Story (Edited)','This is a test story created to verify the story management system. This story describes my journey with kidney disease, from the initial diagnosis to finding hope and strength in the community. It includes challenges, treatments, and the support I received from family and friends. The story aims to inspire others who might be going through similar experiences. [This story has been edited to test the revision system.]','This is a test story created to verify the story management system.','A test story about kidney journey for system verification',NULL,'published','2025-07-04 20:16:59','2025-07-04 22:16:59');
/*!40000 ALTER TABLE `story_contents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `story_revisions`
--

DROP TABLE IF EXISTS `story_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `story_revisions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `story_content_id` int unsigned NOT NULL,
  `editor_id` int unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revision_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `story_content_id` (`story_content_id`),
  KEY `editor_id` (`editor_id`),
  CONSTRAINT `story_revisions_ibfk_1` FOREIGN KEY (`story_content_id`) REFERENCES `story_contents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `story_revisions_ibfk_2` FOREIGN KEY (`editor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `story_revisions`
--

LOCK TABLES `story_revisions` WRITE;
/*!40000 ALTER TABLE `story_revisions` DISABLE KEYS */;
INSERT INTO `story_revisions` VALUES (1,9,5,'My Kidney Journey - A Test Story','This is a test story created to verify the story management system. This story describes my journey with kidney disease, from the initial diagnosis to finding hope and strength in the community. It includes challenges, treatments, and the support I received from family and friends. The story aims to inspire others who might be going through similar experiences.','This is a test story created to verify the story management system.','A test story about kidney journey for system verification','Initial version before editing','2025-07-04 20:16:59');
/*!40000 ALTER TABLE `story_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `level` enum('debug','info','notice','warning','error','critical','alert','emergency') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` json DEFAULT NULL,
  `channel` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'system',
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_level` (`level`),
  KEY `idx_channel` (`channel`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_level_created` (`level`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_logs`
--

LOCK TABLES `system_logs` WRITE;
/*!40000 ALTER TABLE `system_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_activity_logs`
--

DROP TABLE IF EXISTS `user_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_activity_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_id` int unsigned DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_url` text COLLATE utf8mb4_unicode_ci,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `severity` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `additional_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_username` (`username`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_resource_type` (`resource_type`),
  KEY `idx_resource_id` (`resource_id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_severity` (`severity`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_actions` (`user_id`,`action_type`,`created_at`),
  KEY `idx_critical_actions` (`severity`,`action_type`,`created_at`),
  CONSTRAINT `user_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_activity_logs`
--

LOCK TABLES `user_activity_logs` WRITE;
/*!40000 ALTER TABLE `user_activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_profiles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'UTC',
  `language` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `date_format` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Y-m-d',
  `time_format` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'H:i',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `privacy_settings` json DEFAULT NULL,
  `notification_settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_id` (`user_id`),
  KEY `idx_display_name` (`display_name`),
  KEY `idx_language` (`language`),
  KEY `idx_country` (`country`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_user_profiles_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profiles`
--

LOCK TABLES `user_profiles` WRITE;
/*!40000 ALTER TABLE `user_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_registration_logs`
--

DROP TABLE IF EXISTS `user_registration_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_registration_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_status` enum('success','failed','pending') COLLATE utf8mb4_unicode_ci NOT NULL,
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'web',
  `referrer` text COLLATE utf8mb4_unicode_ci,
  `additional_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_email` (`email`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_registration_status` (`registration_status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_registration_source` (`registration_source`),
  KEY `idx_failed_registrations` (`registration_status`,`ip_address`,`created_at`),
  CONSTRAINT `user_registration_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_registration_logs`
--

LOCK TABLES `user_registration_logs` WRITE;
/*!40000 ALTER TABLE `user_registration_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_registration_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int unsigned NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions_new`
--

DROP TABLE IF EXISTS `user_sessions_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions_new` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_cleanup` (`expires_at`,`last_activity`),
  CONSTRAINT `fk_user_sessions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions_new`
--

LOCK TABLES `user_sessions_new` WRITE;
/*!40000 ALTER TABLE `user_sessions_new` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_sessions_new` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','verified_user','translator','moderator','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `two_factor_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `language_preference` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `email_verified_at` datetime DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_email_verified` (`email_verified_at`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@renaltales.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Administrator','admin',NULL,0,'en','2025-07-04 22:02:42',NULL,NULL,'2025-07-04 22:02:42','2025-07-04 22:02:42'),(2,'john_doe','john@renaltales.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','John Doe','user',NULL,0,'en',NULL,NULL,NULL,'2025-07-04 22:02:42','2025-07-04 22:02:42'),(3,'maria_gonzalez','maria@renaltales.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Maria Gonzalez','verified_user',NULL,0,'es','2025-07-04 22:02:42',NULL,NULL,'2025-07-04 22:02:42','2025-07-04 22:02:42'),(4,'peter_novak','peter@renaltales.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Peter Novák','translator',NULL,0,'sk','2025-07-04 22:02:42',NULL,NULL,'2025-07-04 22:02:42','2025-07-04 22:02:42'),(5,'sarah_wilson','sarah@renaltales.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Sarah Wilson','moderator',NULL,0,'en','2025-07-04 22:02:42',NULL,NULL,'2025-07-04 22:02:42','2025-07-04 22:02:42'),(6,'anna_kovacova','anna@renaltales.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Anna Kováčová','verified_user',NULL,0,'sk','2025-07-04 22:02:42',NULL,NULL,'2025-07-04 22:02:42','2025-07-04 22:02:42'),(7,'carlos_rodriguez','carlos@renaltales.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Carlos Rodriguez','user',NULL,0,'es',NULL,NULL,NULL,'2025-07-04 22:02:42','2025-07-04 22:02:42'),(8,'testuser123','testuser@example.com','\\.Acq.7hF0o3ycrSbEXVSmYG5BrwUc7FwIBn6','Test User','user',NULL,0,'en','2025-07-04 22:07:46',NULL,NULL,'2025-07-04 22:07:46','2025-07-04 22:07:46');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_new`
--

DROP TABLE IF EXISTS `users_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_new` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `failed_login_count` int unsigned DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_email_verified` (`email_verified`),
  KEY `idx_status` (`status`),
  KEY `idx_last_login` (`last_login_at`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_new`
--

LOCK TABLES `users_new` WRITE;
/*!40000 ALTER TABLE `users_new` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_new` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-10 11:41:09
