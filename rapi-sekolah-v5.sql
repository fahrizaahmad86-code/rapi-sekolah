-- MySQL dump 10.13  Distrib 9.7.1, for Win64 (x86_64)
--
-- Host: localhost    Database: rapi-sekolah
-- ------------------------------------------------------
-- Server version	9.7.1

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
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `complaints` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned NOT NULL,
  `reporter_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `completion_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_repair` enum('Belum Diperbaiki','Menunggu Spare Part','Sudah Diperbaiki') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Belum Diperbaiki',
  `last_progress_at` timestamp NULL DEFAULT NULL,
  `handler_id` bigint unsigned DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `complaints_room_id_foreign` (`room_id`),
  KEY `complaints_reporter_id_foreign` (`reporter_id`),
  KEY `complaints_handler_id_foreign` (`handler_id`),
  CONSTRAINT `complaints_handler_id_foreign` FOREIGN KEY (`handler_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `complaints_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `complaints_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaints`
--

LOCK TABLES `complaints` WRITE;
/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
INSERT INTO `complaints` VALUES (1,2,1,'kaki meja rusak','complaints/rsW1xpBHfzqBwapR4K3cvQPg0GtijzziGz2JgZf7.jpg','complaints_fixed/8S23bJL5pWBXwZilnBsIEOkS4lv2FNlcNOoqVdDy.jpg','Sudah Diperbaiki','2026-11-09 11:05:36',2,'2026-11-08 13:12:52','2026-11-08 13:12:52','2026-11-09 11:05:36'),(2,2,51,'kaki meja rusak','complaints/7N7fPJ53xCFh8CJg8knzwkYBtVeR0uTGBNxRa5Gy.jpg',NULL,'Menunggu Spare Part','2026-11-08 15:24:26',2,'2026-11-08 15:15:25','2026-11-08 15:15:25','2026-11-08 15:24:26'),(3,9,1,'Rak kaca pecah','complaints/6FVdNvAijoxzlwmqMPxXZVJzxikQVhwhLavv6yXI.jpg',NULL,'Belum Diperbaiki','2026-11-08 15:41:51',97,'2026-11-08 15:41:51','2026-11-08 15:41:51','2026-11-08 15:41:51'),(4,10,1,'lampu mati','complaints/3hzjoQrK5WiaUOzg1HFzeUpc7IyxRTM9gJAED0aI.jpg',NULL,'Belum Diperbaiki','2026-11-09 10:04:04',2,'2026-11-09 10:04:04','2026-11-09 10:04:04','2026-11-09 10:04:04');
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `event_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agenda_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `date` date NOT NULL,
  `start_session` tinyint unsigned NOT NULL,
  `end_session` tinyint unsigned NOT NULL,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `move_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_room_id_foreign` (`room_id`),
  KEY `events_user_id_foreign` (`user_id`),
  CONSTRAINT `events_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,2,1,'rapat pkl','internal','2026-11-08',1,2,'peminjaman/FMWVTX0LlpFxDvC5RFJwzoBVwyCDqMgxTXKD3dUf.jpg','Approved',NULL,'2026-11-08 13:01:32','2026-11-08 13:01:32'),(2,2,51,'Rapat guru','guru','2026-11-08',9,10,'peminjaman/Z2adReBbfiWNpWAO8nhp29AW1fccEv7JupuvAjju.jpg','Approved',NULL,'2026-11-08 15:14:56','2026-11-08 15:16:20'),(3,2,1,'workshop dicoding','internal','2026-11-09',1,2,'peminjaman/maKGo8dpVstS5KNGbm9JFscL41wE8mzFEcXfPeJZ.jpg','Approved',NULL,'2026-11-09 13:25:12','2026-11-09 13:25:12'),(4,2,96,'rapat osis','internal','2026-11-10',1,2,'peminjaman/J5B5QbWN9XW3qrFuTG47hLosOQrRbQ4KN39WLUrW.jpg','Approved',NULL,'2026-11-09 14:10:58','2026-11-09 14:19:49'),(5,4,51,'rapat rpl 1','internal','2026-11-10',1,2,'peminjaman/AU9QC4Jj4XaMT6zDIuRdhxkiO30eW1hIDrxuLlgN.jpg','Pending',NULL,'2026-11-09 14:39:27','2026-11-09 14:39:27');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_08_11_145759_create_rooms_table',1),(6,'2026_08_11_145838_create_complaints_table',1),(7,'2026_08_12_025715_create_events_table',1),(8,'2026_08_12_031533_create_roles_table',1),(9,'2026_11_01_122542_create_notifications_table',1),(10,'2026_11_04_000001_rename_phone_number_to_email_in_users_table',1),(11,'2026_11_04_000002_create_password_otps_table',1),(12,'2026_11_04_171303_add_completion_photo_path_to_complaints_table',1),(13,'2026_11_08_000001_add_agenda_type_to_events_table',2),(14,'2026_11_09_000001_add_phone_number_and_last_progress_at',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('153c8e06-16c3-4abf-a89d-0be30f0c99c4','App\\Notifications\\AssignPicNotification','App\\Models\\User',97,'{\"title\":\"Tugas Perbaikan Baru\",\"message\":\"Anda ditugaskan membenarkan fasilitas di Masjid lantai 2\",\"complaint_id\":3}',NULL,'2026-11-08 15:41:51','2026-11-08 15:41:51'),('1a88b683-1b8a-4a85-9c17-913a40a9b16c','App\\Notifications\\AssignPicNotification','App\\Models\\User',2,'{\"title\":\"Tugas Perbaikan Baru\",\"message\":\"Anda ditugaskan membenarkan fasilitas di DAK\",\"complaint_id\":4}','2026-11-09 10:04:32','2026-11-09 10:04:04','2026-11-09 10:04:32'),('2df05753-c1a8-435d-af2d-2708d76e3777','App\\Notifications\\PeminjamanNotification','App\\Models\\User',51,'{\"title\":\"Status Peminjaman Ruangan\",\"message\":\"Peminjaman kegiatan \\\"Rapat guru\\\" telah disetujui.\",\"event_id\":2}','2026-11-09 10:05:23','2026-11-08 15:16:20','2026-11-09 10:05:23'),('31a9ae50-2c00-42f6-91ee-7ce467f07aa0','App\\Notifications\\AssignPicNotification','App\\Models\\User',2,'{\"title\":\"Tugas Perbaikan Baru\",\"message\":\"Anda ditugaskan membenarkan fasilitas di Wijaya Kusuma\",\"complaint_id\":1}','2026-11-08 15:24:09','2026-11-08 13:12:52','2026-11-08 15:24:09'),('ab0a1e85-b12b-4efc-a3a6-2edb762135dc','App\\Notifications\\PeminjamanNotification','App\\Models\\User',96,'{\"title\":\"Status Peminjaman Ruangan\",\"message\":\"Peminjaman kegiatan \\\"rapat osis\\\" telah disetujui.\",\"event_id\":4}',NULL,'2026-11-09 14:19:49','2026-11-09 14:19:49'),('abe77b49-d445-44c8-9624-7e740285f0e4','App\\Notifications\\AssignPicNotification','App\\Models\\User',2,'{\"title\":\"Tugas Perbaikan Baru\",\"message\":\"Anda ditugaskan membenarkan fasilitas di Wijaya Kusuma\",\"complaint_id\":2}','2026-11-08 15:24:09','2026-11-08 15:15:25','2026-11-08 15:24:09');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_otps`
--

DROP TABLE IF EXISTS `password_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_otps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL DEFAULT '0',
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `password_otps_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_otps`
--

LOCK TABLES `password_otps` WRITE;
/*!40000 ALTER TABLE `password_otps` DISABLE KEYS */;
INSERT INTO `password_otps` VALUES (1,'fahrizaahmad86@gmail.com','$2y$12$zl1we9/JCeYBSvO/nSrE0eW5gmEUZdO3wQzvpT31uaYKWfsNgOHwq',0,'2026-11-09 11:17:01','2026-11-09 11:07:01','2026-11-09 11:07:01');
/*!40000 ALTER TABLE `password_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Waka Sarpras','2026-11-08 11:32:37','2026-11-08 11:32:37'),(2,'Guru','2026-11-08 11:35:04','2026-11-08 11:35:04'),(3,'PIC Ruangan','2026-11-08 11:35:04','2026-11-08 11:35:04'),(4,'Organisasi','2026-11-08 11:35:04','2026-11-08 11:35:04');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pic_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (2,'Wijaya Kusuma',135,'Lantai 1, sebelah timur taman',2,1,'2026-11-08 12:06:45','2026-11-08 12:06:45'),(3,'Lab Komputer 18',36,'Lantai 1, sebelah selatan kantor guru rpl',2,1,'2026-11-08 12:11:09','2026-11-08 12:11:09'),(4,'Lab Komputer 19',36,'Lantai 1, sebelah timur kantin belakang',2,1,'2026-11-08 12:16:37','2026-11-08 12:16:37'),(8,'Masjid lantai 1',100,'Pojok barat daya sekolah',97,1,'2026-11-08 15:38:31','2026-11-08 15:39:03'),(9,'Masjid lantai 2',100,'Pojok barat daya sekolah',97,1,'2026-11-08 15:38:57','2026-11-08 15:38:57'),(10,'DAK',40,'Lantai 1, sebelah barat cafetaria',2,1,'2026-11-08 15:41:03','2026-11-08 15:41:03');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$12$Itk5p6W6RVFZu6r3hlKTae0FSh2DQWcgUmyGZgQtFxEYyGUatxusG','Waka Sarpras','fahrizaahmad86@gmail.com','628123456789',1,'2026-11-08 11:32:38','2026-11-09 09:01:00'),(2,'wilpoy','$2y$12$DzIe9O7KZI.25eUAI3wUpel3U.SmF.L0rTfBt2TF3bOlMo10ThQs.','Wildan','wildan@gmail.com','6289773407010',3,'2026-11-08 11:39:49','2026-11-09 09:53:44'),(3,'raharjo','$2y$12$am6JNheJXK1QHALZAWClZOdwTDwgeSMZHdECYgUZipa85JKg5bWpm','RAHARJO S.IP., M.Pd.','raharjo@gmail.com',NULL,2,'2026-11-08 11:47:27','2026-11-08 12:25:55'),(4,'sunarsih','$2y$12$v3brnKgchjkPkJny4eX4gu.9MSKxdm3AghRcQetjOBFlCt3r2mJJe','SUNARSIH, S.Pd.','sunarsih@gmail.com',NULL,2,'2026-11-08 11:47:28','2026-11-08 12:26:12'),(5,'arispurwantinah','$2y$12$C8pBbbGpagJlFb0LfHBVeOLh.G2eRsdZ/o/B67pSTOyIypHi5EFwu','ARIS PURWANTINAH, S.Pd.','arispurwantinah@gmail.com',NULL,2,'2026-11-08 11:47:28','2026-11-08 12:27:47'),(6,'aisyahwulandari','$2y$12$OY4R/GslMbjjhPsqWUk3Xe0Mx2gA47iU0E3sHqNZkOmyWM8QI4eke','AISYAH WULANDARI, S.Pd','aisyahwulandari@gmail.com',NULL,2,'2026-11-08 11:47:28','2026-11-09 08:25:45'),(7,'srisunartinispd','$2y$12$OxYzH43c8.v0aDN5cw6V7eLcGZWQEuof/wvCOzLv94YlY55OlGgum','SRI SUNARTINI, S.Pd','srisunartini@gmail.com',NULL,2,'2026-11-08 11:47:28','2026-11-09 14:22:06'),(8,'muhamadherupurnomospd','$2y$12$j8MxActQ3On2gM7oTwsSJ.8bkdfl5pcaV5KAWVVpwocQDEakMog7e','MUHAMAD HERU PURNOMO, S.Pd.','heru@gmail.com',NULL,2,'2026-11-08 11:47:28','2026-11-09 14:22:26'),(9,'draekomeiwulan','$2y$12$gg6ZJY1WGuvXHGAAtFLIhuptF7m629f9VLL5Vy7PIg7jLBXeW.b0.','Dra. EKO MEI WULAN','wulan@gmail.com',NULL,2,'2026-11-08 11:47:29','2026-11-09 14:22:46'),(10,'amatsukardispd','$2y$12$UfMNFiLFjgcJlvyH.GcVQO0W6Ypus/UoX.0YfQdGIsnvo.Ew/zzOa','AMAT SUKARDI, S.Pd.','amatsukardi@gmail.com',NULL,2,'2026-11-08 11:47:29','2026-11-09 14:23:14'),(11,'sujarhartonospd','$2y$12$h5vn/OPj2iFGIO9/ubiUwOjn7H0prc2frMQKYlbhBHPDa5cggOoMq','SUJAR HARTONO, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:29','2026-11-08 11:47:29'),(12,'drayatimatunnafiahmm','$2y$12$4xm1OnO7nhBC0s8WJ/Qs6O/vim1tiEYTZmdqTn/m/FlW8WIJ75iZe','Dra. YATIMATUN NAFI`AH, M.M.',NULL,NULL,2,'2026-11-08 11:47:29','2026-11-08 11:47:29'),(13,'wahyunispd','$2y$12$viSnDxuDEoavVrTIOidGputn25aSyw6ArX5xZU81ZVksr.cNpzkoS','WAHYUNI, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:29','2026-11-08 11:47:29'),(14,'drasrisulastri','$2y$12$81SXMClC76mh7cobmjk5musUsH.6HjBb1v.hoFjKijcxGFTLncQKK','Dra. SRI SULASTRI',NULL,NULL,2,'2026-11-08 11:47:30','2026-11-08 11:47:30'),(15,'tatikwardayatispd','$2y$12$hbAPEmsSh.2nnStHYRTChuffQ1LMjAVnDaeoyJsJEOZgEXvXutMV6','TATIK WARDAYATI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:30','2026-11-08 11:47:30'),(16,'radenrarabernadetasriwidyastutispd','$2y$12$XYCeLwC/mBu5QlOZIPzX8ubrJGAazk5lh1N1o8JHI.9Mfhr48DO5e','RADEN RARA BERNADETA SRI WIDYASTUTI, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:30','2026-11-08 11:47:30'),(17,'drssusantodwiprihantorompd','$2y$12$TD2P7nMAfLJuDEKkOhqdg.FbjXWEHfnXEQPL2527S/h6ERQOse8MK','Drs. SUSANTO DWI PRIHANTORO, M.Pd.',NULL,NULL,2,'2026-11-08 11:47:30','2026-11-08 11:47:30'),(18,'ririhdamayantiwisnugiyonosemacc','$2y$12$eQvWmzjkTYDd//Ut0L1r6O7Br0ti/p5xBR7YRocpcKGaaL32qYn/K','RIRIH DAMAYANTI WISNU GIYONO, S.E., M.Acc.',NULL,NULL,2,'2026-11-08 11:47:30','2026-11-08 11:47:30'),(19,'windumahmudspdmeng','$2y$12$6z.xq6suwBLdsr1eoHBNH.6sRfyiqF2rLklzSG4lNDrQCgjKfZy82','WINDU MAHMUD, S.Pd., M.Eng.',NULL,NULL,2,'2026-11-08 11:47:31','2026-11-08 11:47:31'),(20,'trisusilawatistmpd','$2y$12$7ORrQhC5NO8v5XUhqBhhyuq/hCZ2Db4b4um7Ocrv5cbptGMPr75E.','TRI SUSILAWATI, S.T., M.Pd.',NULL,NULL,2,'2026-11-08 11:47:31','2026-11-08 11:47:31'),(21,'danurejospd','$2y$12$X5si69QGGj5g7dHSLbNUReuSQWA078IFrUUp5i5EbGWBKJF5SOzye','DANUREJO, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:31','2026-11-08 11:47:31'),(22,'yuliastutispd','$2y$12$5Rx3W6Cr5XX5ITyxG3whJuMx146xADyJ3KYSXI8tmCxUkV27f5ZIe','YULI ASTUTI, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:31','2026-11-08 11:47:31'),(23,'thereciaindriwahyunispd','$2y$12$7osXZDZuCCbFqpDcZjDMl.c6CqKTVKNduT6DmOrxgUIuiB0OslfJa','THERECIA INDRI WAHYUNI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:31','2026-11-08 11:47:31'),(24,'dramukirah','$2y$12$M4Sfnbfh6JxhUZQkeantl.cBfLuw3nedwPfswHp88H6Z2K1/KRikO','Dra . MUKIRAH',NULL,NULL,2,'2026-11-08 11:47:32','2026-11-08 11:47:32'),(25,'widodospd','$2y$12$ePhdL8KFrXvQxxIYBidpjOb9ctChi2A48z4bo1YmDaZ1xCpTfVZEG','WIDODO, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:32','2026-11-08 11:47:32'),(26,'radenrororetnokristianiindriastutisfk','$2y$12$jO7itDSS5ynwhf3dqrZ2Eeo7CHJS0EoycRwL1c.VgbFr5V4dhGCeO','RADEN RORO RETNO KRISTIANI INDRIASTUTI, S.FK',NULL,NULL,2,'2026-11-08 11:47:32','2026-11-08 11:47:32'),(27,'agnesendangmudjirahayuspdmpd','$2y$12$6wODXjENJaVF.ltIXjjRCOxLRJac7ShksL.V41uF76vOgdG2ECo6S','AGNES ENDANG MUDJI RAHAYU, S.Pd., M.Pd.',NULL,NULL,2,'2026-11-08 11:47:32','2026-11-08 11:47:32'),(28,'yuliarbiatiningsihspd','$2y$12$9m2siuaP0G6ocqXiOqIWXePtRinRgL2UGhZXVT.B.Sfn4NNxmc8gi','YULI ARBIATININGSIH, S.Pd',NULL,NULL,2,'2026-11-08 11:47:33','2026-11-08 11:47:33'),(29,'radenrarauningsuciasihspd','$2y$12$/PoLoFuQAkHsdqgf444KNuglXTjZUogmsnFJTHCOH1C6zELSYcOKO','RADEN RARA UNING SUCIASIH, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:33','2026-11-08 11:47:33'),(30,'wakhidbashorispd','$2y$12$IjH/UjnNJPv.2cGHnuBCp.8sgH9JfWZU.qmf10sKIp1Os3AqjH52u','WAKHID BASHORI, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:33','2026-11-08 11:47:33'),(31,'dianawaliatispdmpd','$2y$12$P18N2kGreS.T6DavSwJAlOTcfkEb5VziEsleqKd7zmdrinwf5lYP2','DIAN AWALIATI, S.Pd.,M.Pd.',NULL,NULL,2,'2026-11-08 11:47:33','2026-11-08 11:47:33'),(32,'warastitinilmiawatispd','$2y$12$BCgkEneU3uCvuwghDPQZ3eTmGoJhMSnoTux9PM16Eq6s.eqw43ozi','WARASTITIN ILMIAWATI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:33','2026-11-08 11:47:33'),(33,'dwilesmiyatispd','$2y$12$K2dIKjM1w/mThyGHXpmSo.kjN8wXBukVMDVRAjAoZmGaFVKi3meSm','DWI LESMIYATI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:34','2026-11-08 11:47:34'),(34,'umikuntarissmpd','$2y$12$0h.uk98UqtVngmsRsVG.ruDDGqxVxfBH4t0u3YzK5DXnOFbeWWNJy','UMI KUNTARI, SS.,M.Pd.',NULL,NULL,2,'2026-11-08 11:47:34','2026-11-08 11:47:34'),(35,'anastasiaratnasulistiyanispd','$2y$12$B9fJcH16zvwImfR3NI9zuukIsk.HlT3Rz724EGpsDEEAwNoTm2eB6','ANASTASIA RATNA SULISTIYANI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:34','2026-11-08 11:47:34'),(36,'drasitikhusnulkhotimah','$2y$12$LfZ1tLhC1xGHG1CnI/W.Dun35qAlrMJbC3A7NHwDRVQsjkkoIovS2','Dra. SITI KHUSNUL KHOTIMAH',NULL,NULL,2,'2026-11-08 11:47:34','2026-11-08 11:47:34'),(37,'mulatispd','$2y$12$4Uc9X0/bvxA2oF7qb0SKk.goDEei5sBy6LyzYc7ZRo./P1mvG5nmK','MULATI, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:34','2026-11-08 11:47:34'),(38,'margiyatispdmpd','$2y$12$4jp2bWhxZpN.z0lAJC9/8uMVjgNXVaRi6ePXqQScGvuYNpmEXiRNy','MARGIYATI, S.Pd., M.Pd.',NULL,NULL,2,'2026-11-08 11:47:35','2026-11-08 11:47:35'),(39,'ristiyantispd','$2y$12$WBm091nEEU9j8GZDVcp8DOXj/OmsOp3aQvy5hMTrd.4syZa3aPLm2','RISTIYANTI, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:35','2026-11-08 11:47:35'),(40,'retnaningsihspd','$2y$12$fcls9DOlMbsSHX1wu2.KuethjVpIVHEpkyGUvQmjsOL918SqhU4Ba','RETNANINGSIH, S.Pd',NULL,NULL,2,'2026-11-08 11:47:35','2026-11-08 11:47:35'),(41,'murdiasihkadarwatispd','$2y$12$TILSGyTYUJxmx6Maf4Ggy.mpfqiEEJP8idZpCWuM/sXlOwz5SFkGi','MURDIASIH KADARWATI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:35','2026-11-08 11:47:35'),(42,'faillarochmayantispdmpd','$2y$12$U8dfv7p6iG/XIfLpa/tJ6OD9wfSTyWiGvUo/Q4aRllgjidtEbF94S','FAILLA ROCHMAYANTI, S.Pd., M.Pd.',NULL,NULL,2,'2026-11-08 11:47:35','2026-11-08 11:47:35'),(43,'nurzanahspd','$2y$12$nYJUHEUWmWsrZMHavBZtM.5WIytgXY4DsSdaInGx2D85HDbcIkbWW','NURZANAH, S.Pd',NULL,NULL,2,'2026-11-08 11:47:36','2026-11-08 11:47:36'),(44,'agathachristiyantimanullangss','$2y$12$GB0l0MNa3xDiG3Ditc2HcuYilQ18ARCxXY8MfPm9bzZhUR37qrsQa','AGATHA CHRISTIYANTI MANULLANG, SS',NULL,NULL,2,'2026-11-08 11:47:36','2026-11-08 11:47:36'),(45,'istifitrianaspd','$2y$12$MSva1xiZY7TVW6cl1ZarIOkatfKHVrj7Sge4eJ3dOqGBpzO.mzmNS','ISTI FITRIANA, S.Pd',NULL,NULL,2,'2026-11-08 11:47:36','2026-11-08 11:47:36'),(46,'sriwidayatispdmpd','$2y$12$DXMrhjRHd4BWBqQNHVqIpeclsSTDFfHJpV6KzhkFMR58V2MtKp.Em','SRI WIDAYATI, S.Pd., M.Pd.',NULL,NULL,2,'2026-11-08 11:47:36','2026-11-08 11:47:36'),(47,'budiyantorosemm','$2y$12$3yv6IgZBD7qx2BoksxxJ.eSQ7HNXJwK3KlJa7VqarMzrMbUbZNFAm','BUDIYANTORO, SE., M.M.',NULL,NULL,2,'2026-11-08 11:47:36','2026-11-08 11:47:36'),(48,'lauhinilmiagustinspd','$2y$12$oGRJvPyUzlLwO/BhGdrvDuh.9Vq9w9sTLFLg3yrhfa0FB2T.3c4qu','LAUHIN ILMI AGUSTIN, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:37','2026-11-08 11:47:37'),(49,'tantriagustianaspd','$2y$12$GIV5/HlI/OJ58E1blDmQYuAaoJWwD2SeP08kw8gCr8KPP9toNTnSW','TANTRI AGUSTIANA, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:37','2026-11-08 11:47:37'),(50,'andriyaniskom','$2y$12$qRDRPhhKZSE67Zh8WIf7oeX42xlQsTh7..6dyP5dV84yB9Z3pm12W','ANDRIYANI, S.Kom.',NULL,NULL,2,'2026-11-08 11:47:37','2026-11-08 11:47:37'),(51,'diah','$2y$12$9mrsWHmuhBiCzMhzF9FCAuoaDnO1EZ130OP13xtEbvsg9kieGz5q.','DIAH UTAMININGSIH, S.T','diah@gmail.com',NULL,2,'2026-11-08 11:47:37','2026-11-08 12:55:31'),(52,'dariyatiskommpd','$2y$12$c4UKQ7AMMj4ZH.YN/ekPY.VaoCsqnx2BE2.ippXsHEUXsq8P3l3gm','DARIYATI, S.Kom.,M.Pd.',NULL,NULL,2,'2026-11-08 11:47:37','2026-11-08 11:47:37'),(53,'sutarmispd','$2y$12$BPQYXVCIKqzCnhyroes20.mnjibSeNNCItztVyGnl8v.G7tnAP1o2','SUTARMI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:38','2026-11-08 11:47:38'),(54,'sulardispd','$2y$12$k5ZFqjrbGWolTBhPYIJOBeh4mK.UHudgZ4fVRaEro3MIljd74olNa','SULARDI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:38','2026-11-08 11:47:38'),(55,'rosyidaramadhanispd','$2y$12$LCk0RLGzN/lCWmZm/oxwqujWGZCJ.e6bswms0T0zuRvjg7aR0MmkO','ROSYIDA RAMADHANI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:38','2026-11-08 11:47:38'),(56,'srisunarsihspd','$2y$12$tsSXkcSLX/2zrYapJAV7muP0PEuMnDTT..fZ5vNAkL/xDJGvkdyhS','SRI SUNARSIH, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:38','2026-11-08 11:47:38'),(57,'yanuiswantaraspdkor','$2y$12$mcDcylDwlFuAImTHs2wXdeQ9WIpzYMH..GHYUaJg3LzGpCOSoekWC','YANU ISWANTARA, S.Pd.Kor.',NULL,NULL,2,'2026-11-08 11:47:38','2026-11-08 11:47:38'),(58,'tentremyuniatilestarispd','$2y$12$479D2kiPO3PxwWtHNQEfvuJRM1YTThN5ZNRoHN1CumbHMTK7gSp7m','TENTREM YUNIATI LESTARI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:39','2026-11-08 11:47:39'),(59,'kurniasafitrispd','$2y$12$G9NrX9DigrAUL4Nt.4KWE.RRsQxVAK1kz0sS88Ai/cY7SvFYgMIuK','KURNIA SAFITRI, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:39','2026-11-08 11:47:39'),(60,'ahmadmuhlashinspd','$2y$12$FBHAdMOySPr/.d3USKgFIOlSRDwqt1Htd3DFsllHw4AlaeoX1G1Sq','AHMAD MUHLASHIN, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:39','2026-11-08 11:47:39'),(61,'watispdi','$2y$12$aNANDG1fr4THDehX86NDm.HaG1wuUrWrzJACwPCSrEsnmGrdYJvwW','WATI, S.Pd I.',NULL,NULL,2,'2026-11-08 11:47:39','2026-11-08 11:47:39'),(62,'harisbudiawanspd','$2y$12$3SRE7vtFlBKaakehzLTZ3uXQXvqLz0ZuXUe09niWh1s6xMTsrdY/W','HARIS BUDIAWAN, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:39','2026-11-08 11:47:39'),(63,'ryawidyaningtyasspd','$2y$12$m0DsKUmxRkqc/78TXaIQlejX3LuVP0bdanAWD2S46TCBXsQI6BRWK','RYAWIDYANINGTYAS, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:40','2026-11-08 11:47:40'),(64,'nurhidayatsulistiawanspd','$2y$12$DVj69z/0clbWXe7eV6rlvO/TSz.x9PD8Fnht3HhJ2gwub4eM9yLeK','NURHIDAYAT SULISTIAWAN, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:40','2026-11-08 11:47:40'),(65,'rinibhintartispd','$2y$12$B6Pk.Kkx1sfdh0Tsa7u0.eDbpy5vVduKcMPc3C52xmqDINpSdolyG','RINI BHINTARTI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:40','2026-11-08 11:47:40'),(66,'srinurarafahspd','$2y$12$fIbkBKTD.XJHKnGXlbUEH.je/ikq5McgQvad7n9R5RXoj./YbKMJK','SRI NUR ARAFAH, S.Pd',NULL,NULL,2,'2026-11-08 11:47:40','2026-11-08 11:47:40'),(67,'evayuniastutispd','$2y$12$DF5pJHEseLno93ClPjx6YOzfQ12yc7rNgYnwFdLxYIYZZfCKtpX6a','EVA YUNI ASTUTI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:41','2026-11-08 11:47:41'),(68,'hermicahyawatispdi','$2y$12$ZMuaUryW/8nnpri206PZL.5C5cAodUyaX/H.oXBf6o6Z7HU7QPCva','HERMI CAHYAWATI, S.Pd.I',NULL,NULL,2,'2026-11-08 11:47:41','2026-11-08 11:47:41'),(69,'rohmaddwiyantospd','$2y$12$EeHdO47QTXAcMqxWCtzJweeTyYQ1i3V2R9/K1TZas3HfCHneQNlCi','ROHMAD DWIYANTO, S.Pd',NULL,NULL,2,'2026-11-08 11:47:41','2026-11-08 11:47:41'),(70,'eridhadewisepitasarispd','$2y$12$WCMXnJHr7AglKuKRIc6gt.WMzQ4/hDAqcZJBNrPwYcloh7vylDH4C','ERIDHA DEWI SEPITASARI, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:41','2026-11-08 11:47:41'),(71,'noviarahmalinaspd','$2y$12$Y0dzbt0SJRjJD35/hIvMa.PVbdbshMkaq9EXlQ29gJI.QHSh6PQYO','NOVIA RAHMALINA, S.Pd',NULL,NULL,2,'2026-11-08 11:47:41','2026-11-08 11:47:41'),(72,'renymurnihidayatispd','$2y$12$AxiagMhrr02NCpmDxL/d7.6AZjxJ2sx6DkKOWU6tzQB92.iDeJk9u','RENY MURNI HIDAYATI, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:42','2026-11-08 11:47:42'),(73,'rusdiyantospd','$2y$12$eoeEDCRarGIooJbBm4RD8.T.mfqevSUMAm528BgFwhLjWLzgxMu/e','RUSDIYANTO, S. Pd.',NULL,NULL,2,'2026-11-08 11:47:42','2026-11-08 11:47:42'),(74,'suratimahspdi','$2y$12$AJG/u.IpaNL4kICdD3Vucem62oNcCvlkhp623vUty6rUPCd0DFwVO','SURATIMAH, S. Pd. I',NULL,NULL,2,'2026-11-08 11:47:42','2026-11-08 11:47:42'),(75,'putriwulanjarispd','$2y$12$BcFQr1ij/AiCDwzzmd92deYMeKKPUVum3hNcMeNEOw7Vw2XUQ9Hmu','PUTRI WULANJARI, S. Pd.',NULL,NULL,2,'2026-11-08 11:47:42','2026-11-08 11:47:42'),(76,'duwiriantospd','$2y$12$oDaubDXdHcpJ3tUU0nECb.iWaXdOOReoQHmMKQOIjmQGZbuUI3sQO','DUWI RIANTO, S. Pd.',NULL,NULL,2,'2026-11-08 11:47:42','2026-11-08 11:47:42'),(77,'meniklestarispdi','$2y$12$EqPJa5PJuVFCJOIbpdnGmuFHz0GLS1PFwNm5J3hKJ2kX811fefLUm','MENIK LESTARI, S. Pd. I',NULL,NULL,2,'2026-11-08 11:47:43','2026-11-08 11:47:43'),(78,'nindaapriliaspd','$2y$12$.PGHooLa9PXNvL6KPG3I5.rEKeCMxrDV.32tXVvyPUzaObljjOJFi','NINDA APRILIA, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:43','2026-11-08 11:47:43'),(79,'muhammadfurqonssos','$2y$12$ENR4q.2jJiUE.sKpDVokuuFx61uTfjRrERNmxoUNq2StAJRqjsaQK','MUHAMMAD FURQON, S.Sos',NULL,NULL,2,'2026-11-08 11:47:43','2026-11-08 11:47:43'),(80,'candrarahadianspd','$2y$12$eLqRLXQG7B8UE3Lvudkj..BLsW7WKhKUVtAOcMnjnvK0smDSE.FYm','CANDRA RAHADIAN, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:43','2026-11-08 11:47:43'),(81,'juniasarispd','$2y$12$zfjKqFBdstqaZxyzQTz5r.4M8mZvzVVXJ5JhVp2LRkSCvptSpmzuO','JUNIA SARI, S. Pd.',NULL,NULL,2,'2026-11-08 11:47:43','2026-11-08 11:47:43'),(82,'risarestiafrianispd','$2y$12$J7uL5BvNL9UMc8wkmhkzFen6Ny.yVL8OJS1ofs.gY3qGgeb2IcwuW','RISA RESTI AFRIANI, S. Pd.',NULL,NULL,2,'2026-11-08 11:47:44','2026-11-08 11:47:44'),(83,'alivisazkiahawiespd','$2y$12$xRyJusxN.cG/Z1ftCo2nx.jy0AgMR.ulHm94xJBdlHBo7TwnxVwqG','ALIVI SAZKIA HAWIE, S. Pd.',NULL,NULL,2,'2026-11-08 11:47:44','2026-11-08 11:47:44'),(84,'enengfitrianipuspitowatispdsi','$2y$12$3H62zP/Kp8PA2Q.cdBOGIuQKyOf9QNzriAmYwhnKMjeo39ssZz7nC','ENENG FITRIANI PUSPITOWATI, S.Pd.Si.',NULL,NULL,2,'2026-11-08 11:47:44','2026-11-08 11:47:44'),(85,'titisariskom','$2y$12$j5Ku66cznlxybaYcAEYg7OPsw.wuEDQCSDnLk0qkHfJpruaDJDC9K','TITI SARI, S.Kom.',NULL,NULL,2,'2026-11-08 11:47:44','2026-11-08 11:47:44'),(86,'ajengfitrisaraswatispd','$2y$12$hi21eFRqfWfAV.lHoOHi.uA7ZbpxYWkWVq9KJs6ukSDINIwo2bpdu','AJENG FITRI SARASWATI, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:44','2026-11-08 11:47:44'),(87,'yulitaekawinajengspd','$2y$12$HiSu4La/VcyFx3Rou.5G8.Xuh8AbXxPKrM/SeNVStDNnpZOMkxuPy','YULITA EKAWINAJENG, S.Pd.',NULL,NULL,2,'2026-11-08 11:47:45','2026-11-08 11:47:45'),(88,'rahmawatiyogapamungkasspdmpd','$2y$12$4azUA8T/58Zm8/BpPFl7beU8yLb7VpVYs27IQqM2PHFOp8XpcW2Z.','RAHMAWATI YOGA PAMUNGKAS, S.Pd., M.Pd',NULL,NULL,2,'2026-11-08 11:47:45','2026-11-08 11:47:45'),(89,'fianinurajipangastutispd','$2y$12$.p5XoPQKj7leudUfbNabBuVXjpncW8tn14nqwvkrv18OVoAmxtZQG','FIANI NUR AJI PANGASTUTI, S.Pd',NULL,NULL,2,'2026-11-08 11:47:45','2026-11-08 11:47:45'),(90,'sellyananurulazizahspd','$2y$12$6CURnOUXG364TNU2xViJkO3Q9eW2nVZAbo.kBE2fF/Tvp.dJPcg.a','SELLYANA NURUL AZIZAH, S.Pd',NULL,NULL,2,'2026-11-08 11:47:45','2026-11-08 11:47:45'),(91,'ditarizkidewintasari','$2y$12$pyy2DkEutKtMszucbiVCq.hUNo9E6dZXlZHWprsfjuC1j50lPkX/2','DITA RIZKI DEWINTA SARI, M.Pd.','sample@gmail.com',NULL,2,'2026-11-08 11:47:45','2026-11-08 13:08:36'),(92,'dianarohmaniah','$2y$12$lBHlojlU3/opU4JivUHE9O0zIXEZ.bRxd7OYuso84rLFI.gVHW5V6','DIANA ROHMANIAH','diana@gmail.com',NULL,2,'2026-11-08 11:47:46','2026-11-08 13:08:11'),(93,'dindaayudewanti','$2y$12$NhhN2AMyWIpIAJ0Ijkr9tOfPdCV/gyf91l3wurIUtCxiMNR/xYBk.','DINDA AYU DEWANTI, S.Kom','dinda@gmail.com',NULL,2,'2026-11-08 11:47:46','2026-11-08 13:07:52'),(96,'osis','$2y$12$6rHRJW3CCT7dI/0wlMPteu0/YvmycoDFKgYs/i3mYMhDKCJOvEHDe','Organisasi','osis@gmail.com',NULL,4,'2026-11-08 11:50:31','2026-11-08 11:50:31'),(97,'budi','$2y$12$/LqXIXKjAGco4L18QPzaj.iA13kH61UpA3o2xQa5QhBvVcmPx8vd2','Budi Santoso','budi@gmail.com','628977340701',3,'2026-11-08 12:56:16','2026-11-09 09:53:56');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 22:14:33
