-- MySQL dump 10.13  Distrib 8.0.36, for Linux (x86_64)
--
-- Host: localhost    Database: issue_tracker
-- ------------------------------------------------------
-- Server version	8.0.36-0ubuntu0.22.04.1

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
-- Table structure for table `admin_ratings`
--

DROP TABLE IF EXISTS `admin_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_ratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `admin_role` enum('Organization Admin','Global Admin') NOT NULL,
  `rating` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `admin_ratings_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admin_ratings_chk_1` CHECK (((`rating` >= 1) and (`rating` <= 5)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_ratings`
--

LOCK TABLES `admin_ratings` WRITE;
/*!40000 ALTER TABLE `admin_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Bug Report'),(2,'Technical Issue'),(3,'Billing'),(4,'Tech support');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `user_id` int NOT NULL,
  `resolved` enum('Yes','No') NOT NULL,
  `satisfaction` enum('Excellent','Good','Poor') NOT NULL,
  `comments` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`),
  CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
INSERT INTO `feedback` VALUES (1,2,6,'Yes','Excellent','well done','2024-06-19 07:15:05');
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `recipient_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `recipient_id` (`recipient_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,6,4,'hi','2024-06-19 12:12:00'),(2,6,4,'hi','2024-06-19 12:12:00'),(3,4,6,'hello','2024-06-19 12:12:30'),(4,4,6,'hello','2024-06-19 12:12:30'),(5,6,4,'i have an issue with ticket id 2','2024-06-19 12:21:01'),(6,6,4,'i have an issue with ticket id 2','2024-06-19 12:21:01'),(7,6,4,'it was not well solved\r\n','2024-06-19 12:23:05'),(8,6,4,'it was not well solved\r\n','2024-06-19 12:23:05'),(9,6,7,'hi\r\n','2024-06-19 12:32:57'),(10,6,7,'hi\r\n','2024-06-19 12:32:57'),(11,6,7,'hello\r\n','2024-06-19 12:33:10'),(12,6,7,'hello\r\n','2024-06-19 12:33:10'),(13,7,6,'hello how can i help you today','2024-06-19 12:43:51'),(14,7,6,'hello how can i help you today','2024-06-19 12:43:52'),(15,7,20,'hi','2024-06-19 12:54:37'),(16,7,20,'hi','2024-06-19 12:54:37'),(17,7,18,'hello','2024-06-19 12:57:02'),(18,7,18,'hello','2024-06-19 12:57:02'),(19,6,4,'no answer?','2024-06-19 13:11:49'),(20,4,6,'hello sorry been busy','2024-06-19 13:19:03'),(21,4,6,'we are sorting your issue','2024-06-19 13:19:19'),(22,7,6,'you are not answering','2024-06-19 13:29:03'),(23,7,6,'morning','2024-06-21 07:44:03');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(100) NOT NULL,
  `physical_address` varchar(255) NOT NULL,
  `postal_address` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_name` (`company_name`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizations`
--

LOCK TABLES `organizations` WRITE;
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
INSERT INTO `organizations` VALUES (1,'Techpoint Investments','123 Tech Street','P.O. Box 123','info@techpoint.com','1234567890','logo.png','2024-06-11 07:23:12'),(3,'Zurion Technologies',' Mombasa teacher Plaza 2nd floor','P.O. Box 123456','info@zuriotech.com','0737894566','logo1.png','2024-06-11 07:28:25'),(4,'Kerry Lens','2345 Uhuru street','P.O BOX 7900','kerrylens@gmail.com','0756325537','uploads/kl.jpeg','2024-06-11 11:33:21'),(5,'Nairobi Homes','2345 Uhuru street','P.O BOX 7900','nairobihomes@gmail.com','0712345678','uploads/people.png','2024-06-11 11:35:11'),(6,'Helix Corporate','2344 Garden city','P.O BOx 79900','info@helix.com','0788006655','uploads/helix.png','2024-06-11 12:28:16'),(7,'ddd','33232ccc','ede44','fwsw4@gmail.com','0700506530','uploads/zu.png','2024-06-11 14:07:34'),(8,'Kartasi industried','2344 Garden city','Nairobi, Kenya','kartasi@gmail.com','0708842213','uploads/kar.jpeg','2024-06-11 14:35:00'),(11,'Manoti Homes','4657 Uhuru street','P.O BOX 790021','info@manoti.com','0785643322','uploads/manoti.jpeg','2024-06-18 12:35:27'),(12,'Fello','4657 Uhuru street','P.O BOx 79900','feloo@gmail.com','0740100258','uploads/flower-removebg-preview.png','2024-06-24 10:57:00');
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `responses`
--

DROP TABLE IF EXISTS `responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `responses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `admin_id` int NOT NULL,
  `response` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `responses_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`),
  CONSTRAINT `responses_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `responses`
--

LOCK TABLES `responses` WRITE;
/*!40000 ALTER TABLE `responses` DISABLE KEYS */;
INSERT INTO `responses` VALUES (1,11,7,'assigned to jane',NULL,'','2024-06-21 09:39:50');
/*!40000 ALTER TABLE `responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `revokes`
--

DROP TABLE IF EXISTS `revokes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `revokes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `reason` text NOT NULL,
  `revoked_by` int NOT NULL,
  `revoked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `revoked_by` (`revoked_by`),
  CONSTRAINT `revokes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `revokes_ibfk_2` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `revokes`
--

LOCK TABLES `revokes` WRITE;
/*!40000 ALTER TABLE `revokes` DISABLE KEYS */;
INSERT INTO `revokes` VALUES (1,21,'misconduct',7,'2024-06-24 07:54:42');
/*!40000 ALTER TABLE `revokes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `priority` enum('Low','Medium','High') NOT NULL,
  `assigned_to` int DEFAULT NULL,
  `status` enum('Pending','Assigned','Responded','Resolved','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `category` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT 'Unknown',
  `resolved_at` datetime DEFAULT NULL,
  `comments` text,
  `service_rating` enum('Excellent','Good','Poor') DEFAULT NULL,
  `resolved` enum('Yes','No') DEFAULT NULL,
  `cancellation_reason` text,
  `attachment` varchar(255) DEFAULT NULL,
  `assigned_admin` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (1,'overcharge','Low',NULL,'Cancelled','2024-06-15 16:19:46',6,'Billing','Unknown',NULL,NULL,NULL,NULL,'mistake','uploads/kar.jpeg',NULL),(2,'hell','Low',NULL,'Resolved','2024-06-15 16:48:44',6,'General Inquiry','Unknown','2024-06-18 09:25:59',NULL,NULL,NULL,NULL,NULL,4),(3,'general inquiry','Low',NULL,'Resolved','2024-06-16 11:57:54',6,'General Inquiry','Zurion Technologies','2024-06-18 11:19:47',NULL,NULL,NULL,NULL,'uploads/kl.jpeg',NULL),(4,'System is down','Low',NULL,'Responded','2024-06-18 06:48:56',6,'Technical Issue','Zurion Technologies',NULL,NULL,NULL,NULL,NULL,'uploads/kar.jpeg',NULL),(5,'when is the next servicing','Low',NULL,'Resolved','2024-06-18 07:38:16',18,'General Inquiry','Techpoint Investments','2024-06-19 12:22:46',NULL,NULL,NULL,NULL,'uploads/zu.png',NULL),(6,'machine is down','Low',NULL,'Assigned','2024-06-18 10:13:09',6,'Technical Issue','Zurion Technologies',NULL,NULL,NULL,NULL,NULL,'uploads/zuriontech.com195Zurion-technology-logo-white-surface.png',7),(7,'test1','Low',NULL,'Resolved','2024-06-19 05:55:43',6,'Technical Issue','Zurion Technologies','2024-06-19 11:19:31',NULL,NULL,NULL,NULL,'',4),(8,'assign ticket test2','Low',NULL,'Assigned','2024-06-19 06:18:31',20,'Technical Issue','Techpoint Investments',NULL,NULL,NULL,NULL,NULL,'',4),(9,'final test','Low',NULL,'Responded','2024-06-21 07:33:29',6,'General Inquiry','Zurion Technologies',NULL,NULL,NULL,NULL,NULL,'',4),(10,'dgfygg','Low',NULL,'Responded','2024-06-21 09:31:05',6,'Technical Issue','Zurion Technologies',NULL,NULL,NULL,NULL,NULL,'',4),(11,'sxhxgfh','Low',NULL,'Responded','2024-06-21 09:37:41',6,'Technical Issue','Zurion Technologies',NULL,NULL,NULL,NULL,NULL,'',7),(12,'cancelreasontest\r\n','Low',NULL,'Cancelled','2024-06-24 08:53:59',6,'Technical Issue','Zurion Technologies',NULL,NULL,NULL,NULL,'sorry it was a mistake','',NULL);
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Customer','Organization Admin','Global Admin') NOT NULL DEFAULT 'Customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) DEFAULT '1',
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `company_name` (`company_name`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`company_name`) REFERENCES `organizations` (`company_name`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,'Sam','Dylan','Techpoint Investments','samdylan@gmail.com','0700506530','$2y$10$kRQMCV0EIeWTuSj0vazIZONdxrUKklkYujjoygl9bVFTfOlR4GB/a','Global Admin','2024-06-11 07:24:49',1,'active'),(5,'John','Doe','Zurion Technologies','johndoe@gmail.com','0789665433','$2y$10$3c.aCszMU3/pZJttBdqUQeKVHg583NMgB4ZJ2ICut1UcN.21QPz1O','Organization Admin','2024-06-11 07:28:56',1,'active'),(6,'Luqmann','mustafa','Zurion Technologies','luqmanm@gmail.com','0987666558','$2y$10$XEMHJZ8kEZo/8sj4pReuYeVQTy0P314So4aZT6zEns4XwbhduQwzy','Customer','2024-06-11 07:35:29',1,'active'),(7,'Jane','Doe','Zurion Technologies','janedoe@gmail.com','0700506530','$2y$10$I25lsdx9VDx6rabysTFNgeJWQ0ZHIGpneFXgvAje7CKcqKYLIKzKS','Global Admin','2024-06-11 08:46:07',1,'active'),(9,'Aisha','hamadi','Helix Corporate','aishah@gmail.com','0788654322','$2y$10$qcnugec.8Ym04tbfAWyLgugDldJlzgGotKitPI4ch7s9e3e0MRALu','Global Admin','2024-06-12 09:04:25',1,'active'),(10,'Samantha','Jones','Kerry Lens','sjones@gmail.com','0746473211','$2y$10$du80jGF/2lmjfGrk34AsnuqhydWJnOcfFmbbDBeRtOCsknzSZimk6','Organization Admin','2024-06-12 09:16:22',1,'active'),(11,'fiona','mwakioh','Techpoint Investments','fiona@gmail.com','0789554377','$2y$10$bm5bNiR1Q6nzGlVks6osJO55hdau5GIA0K/uRkceK04R9SfLBN8Cm','Organization Admin','2024-06-12 09:52:08',1,'active'),(16,'Eddy','Brians','Zurion Technologies','eddb@gmail.com','0789556325','$2y$10$ISpUDUTdE4V8QcDyOaiMOuw./cVUMcG21BsSwNgT7iyHeXg.HVnVO','Customer','2024-06-13 09:15:30',1,'active'),(17,'James','Fil','Kartasi industried','james@gmail.com','0790776634','$2y$10$KAK0w7puOWEXzuly7vOqh.Qnk1Lx6up4Yz0GvCWZogT1G3177h942','Organization Admin','2024-06-14 09:31:33',1,'active'),(18,'Simon','Poe','Techpoint Investments','simonpoe@gmail.com','0780336721','$2y$10$7VbMskhe2Wk//cNmvjWuHO0fsgr5ucgvju02lUpWVQIE0wkLr7D52','Customer','2024-06-15 13:29:17',1,'active'),(19,'Jack','Doe','Zurion Technologies','jackdoe@gmail.com','0740100258','$2y$10$yEUgBLVpl7IULl7Y2sOrme8hZblaQbKZ4jC6CwMDa1FpvAX6ly3VC','Customer','2024-06-18 09:01:46',1,'active'),(20,'Chris','Kaiga','Techpoint Investments','chriskaiga@gmail.com','0789045622','$2y$10$6gUKvkAOvYaHgzUNRR9JbOaw6G/02wFa8ugZwJMA7FMEoQl1OxyVW','Customer','2024-06-18 11:20:02',1,'active'),(21,'Kevin','Kibet','Techpoint Investments','kevin@gmail.com','0787199258','$2y$10$6KtAwRzs1Vjh74xNEabzqecpKavhR0h1ug3Qwj2WDJN0ZNpB5xrJC','Customer','2024-06-18 12:23:20',1,'active');
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

-- Dump completed on 2024-06-24 14:23:15
