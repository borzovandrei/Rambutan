-- MySQL dump 10.13  Distrib 5.7.19, for osx10.12 (x86_64)
--
-- Host: localhost    Database: rambutan
-- ------------------------------------------------------
-- Server version	5.7.19

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat` (
  `id_msg` int(11) NOT NULL AUTO_INCREMENT,
  `chat_room` int(11) DEFAULT NULL,
  `message` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id_msg`),
  KEY `IDX_659DF2AAD403CCDA` (`chat_room`),
  CONSTRAINT `FK_659DF2AAD403CCDA` FOREIGN KEY (`chat_room`) REFERENCES `chatroom` (`id_room`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat`
--

LOCK TABLES `chat` WRITE;
/*!40000 ALTER TABLE `chat` DISABLE KEYS */;
INSERT INTO `chat` VALUES (1,2,'Склад сегодня не работает((','john.doe','2017-10-11 11:32:54'),(7,2,'почему?','john.doe','2017-10-23 20:41:33'),(8,2,'потому','john.doe','2017-10-23 20:41:46');
/*!40000 ALTER TABLE `chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatroom`
--

DROP TABLE IF EXISTS `chatroom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatroom` (
  `id_room` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_room`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatroom`
--

LOCK TABLES `chatroom` WRITE;
/*!40000 ALTER TABLE `chatroom` DISABLE KEYS */;
INSERT INTO `chatroom` VALUES (1,'Доставка'),(2,'Склад');
/*!40000 ALTER TABLE `chatroom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `products_id` int(11) DEFAULT NULL,
  `user` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` longtext COLLATE utf8_unicode_ci NOT NULL,
  `approved` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9474526C6C8A81A9` (`products_id`),
  CONSTRAINT `FK_9474526C6C8A81A9` FOREIGN KEY (`products_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comment`
--

LOCK TABLES `comment` WRITE;
/*!40000 ALTER TABLE `comment` DISABLE KEYS */;
INSERT INTO `comment` VALUES (1,2,'Человек','Очень вкусные',1,'2017-10-06 02:06:23','2017-10-19 00:00:00'),(2,2,'asd','asdasda',1,'2017-10-12 13:25:17','2017-10-12 13:25:17'),(3,1,'Гость','Покупал на прошлой недели. Очень сочные',1,'2017-10-12 14:52:39','2017-10-12 14:52:39'),(4,1,'john.doe','ываываыв',1,'2017-10-18 14:09:50','2017-10-18 14:09:50'),(5,1,'user','dsdf',1,'2018-01-25 15:44:53','2018-01-25 15:44:53'),(6,3,'john.doe','ASD',1,'2018-01-27 09:08:46','2018-01-27 09:08:46');
/*!40000 ALTER TABLE `comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `likes` int(11) NOT NULL,
  `author` int(11) DEFAULT NULL,
  `products` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_49CA4E7DBDAFD8C8` (`author`),
  KEY `IDX_49CA4E7DB3BA5A5A` (`products`),
  CONSTRAINT `FK_49CA4E7DB3BA5A5A` FOREIGN KEY (`products`) REFERENCES `products` (`id`),
  CONSTRAINT `FK_49CA4E7DBDAFD8C8` FOREIGN KEY (`author`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `likes`
--

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
INSERT INTO `likes` VALUES (1,0,3,1),(2,1,3,1),(3,1,3,3),(4,1,3,14),(5,1,3,2),(6,0,3,15),(7,1,3,7),(8,1,3,19),(14,0,3,10),(15,0,3,13),(16,1,1,1),(17,1,1,18),(18,0,1,2),(19,1,1,14),(20,1,1,17),(21,0,1,19),(22,0,1,16),(23,1,1,12);
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `measure`
--

DROP TABLE IF EXISTS `measure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `measure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `measure`
--

LOCK TABLES `measure` WRITE;
/*!40000 ALTER TABLE `measure` DISABLE KEYS */;
INSERT INTO `measure` VALUES (1,'кг'),(2,'л'),(4,'шт');
/*!40000 ALTER TABLE `measure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orderitem`
--

DROP TABLE IF EXISTS `orderitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orderitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item` int(11) NOT NULL,
  `orderprod` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sum` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orderitem`
--

LOCK TABLES `orderitem` WRITE;
/*!40000 ALTER TABLE `orderitem` DISABLE KEYS */;
INSERT INTO `orderitem` VALUES (1,10,'6902dd2762041038bb115f2b6d84b881',2),(2,3,'6902dd2762041038bb115f2b6d84b881',1),(3,2,'6902dd2762041038bb115f2b6d84b881',3),(4,1,'6902dd2762041038bb115f2b6d84b881',1),(9,1,'87d5d795870a90f5d2e22ca84e3a67a4',3),(10,2,'093109569a02abe62effb1ee9df4eddf',4),(11,2,'9ef8de70b620f3475dfa56e38201299a',4),(12,2,'01f789ab50e9a38ad969238e13c27d26',4),(13,1,'56bb27c3abe3435cc86ae329622a606d',3),(14,1,'3a758ca2c37a012f420147355f99170e',3),(15,1,'002d50f7d759ae54302f12fb0a14cd3a',3),(16,8,'2676766c00a24e66ded296c86d6422e8',1),(17,7,'2676766c00a24e66ded296c86d6422e8',2),(18,1,'2676766c00a24e66ded296c86d6422e8',1),(19,3,'03b73ef253da443c7e592979376820db',1),(20,2,'03b73ef253da443c7e592979376820db',1),(21,1,'03b73ef253da443c7e592979376820db',1),(22,2,'74fd5d28482b785e92ea6e52f41a28c4',3),(23,1,'74fd5d28482b785e92ea6e52f41a28c4',3),(24,1,'26064e548b7df7c9702e111efdb1092c',2),(25,2,'bfc706f06db7c6f191df8668a973344b',3),(26,1,'bfc706f06db7c6f191df8668a973344b',3),(27,1,'1fb7a051d13dfcda92aa489905376946',5),(28,1,'346c36aa87adef836f57d4d1439a2ef0',2),(29,7,'b3cffdd19afdfd6d4df64a6a518cf89e',6),(30,2,'90b44d2a681f87d0296316e54bd8af5c',3),(31,2,'fcbc2df2295bccc9734b6ced7c2c4911',1),(32,1,'c0ebefca219a0d43a8d16ade10eafb0e',2),(33,14,'071b335d856acb94ff27835823adffca',2),(34,12,'071b335d856acb94ff27835823adffca',3),(35,13,'071b335d856acb94ff27835823adffca',5),(36,1,'8c0e9179f73a1198bd84db5d7392543f',4),(37,9,'c798d27e02385acb3683519bf1763836',5),(38,7,'c798d27e02385acb3683519bf1763836',1),(39,15,'c798d27e02385acb3683519bf1763836',1),(40,8,'c798d27e02385acb3683519bf1763836',1),(41,8,'ed2631e096548508a07ba8ffb1f96d0b',3),(42,10,'ed2631e096548508a07ba8ffb1f96d0b',3),(43,2,'ed2631e096548508a07ba8ffb1f96d0b',1),(44,1,'11d85457bb18cdbfcd62322885b90e28',4),(45,9,'2fd6f29ed55fbcad76b621c85bbf371e',1),(46,3,'2fd6f29ed55fbcad76b621c85bbf371e',1),(47,2,'2fd6f29ed55fbcad76b621c85bbf371e',1),(48,1,'2fd6f29ed55fbcad76b621c85bbf371e',1),(49,1,'b285e3374629de9545d287d9179789bd',1),(50,7,'8a5e1d5e3eff7018e1e28aab1b5156fd',3),(51,8,'8a5e1d5e3eff7018e1e28aab1b5156fd',1),(52,2,'8a5e1d5e3eff7018e1e28aab1b5156fd',1),(53,7,'94115d28b69a94c948e41ecd24d12b1c',1),(54,1,'cd3225f7689d41fba973ef79388974c1',1),(55,14,'919bc7ad69ab0a4dc51ea3b9a6a3f4f6',4),(56,16,'919bc7ad69ab0a4dc51ea3b9a6a3f4f6',1),(57,15,'919bc7ad69ab0a4dc51ea3b9a6a3f4f6',1),(58,2,'fd28fc8ad65ec94cfa1ff513ca9a9454',1),(59,1,'fd28fc8ad65ec94cfa1ff513ca9a9454',1),(60,8,'68357f6c91932fa83c6bd28b7e4dcef4',4),(61,1,'73b182e873359ff9463a5a900602e477',4),(62,17,'49edb0c024813c1938b61e168d0142db',8),(63,14,'49edb0c024813c1938b61e168d0142db',7),(64,16,'49edb0c024813c1938b61e168d0142db',6),(65,15,'49edb0c024813c1938b61e168d0142db',5),(66,13,'49edb0c024813c1938b61e168d0142db',4),(67,8,'49edb0c024813c1938b61e168d0142db',3),(68,10,'49edb0c024813c1938b61e168d0142db',1),(69,7,'49edb0c024813c1938b61e168d0142db',2),(70,1,'b851b9b6df579f0a84a16adecb87e825',3),(71,1,'b48d9cdf1327f8a27c8feb1daf4318af',1),(72,10,'a1d1cef7561c777975a500dc2774e20f',3),(73,1,'c993792f3a11b142904abd3da2c62d0c',2),(74,1,'eeb90d3f11bc714e5c4bef32773009f8',1),(75,1,'614c071cebf84d4fa8aaffc7375bb72a',2),(76,2,'614c071cebf84d4fa8aaffc7375bb72a',1),(77,1,'7163b95a89b7b5fcc29a27926b29cc3d',2),(78,3,'7163b95a89b7b5fcc29a27926b29cc3d',4),(79,2,'7163b95a89b7b5fcc29a27926b29cc3d',3),(80,1,'a8df5f01c87b7b825c31b5af590f5555',1),(81,2,'a8df5f01c87b7b825c31b5af590f5555',2),(82,3,'a8df5f01c87b7b825c31b5af590f5555',1),(83,2,'ac4db4521eec0a59d2c098e744e2327b',2),(84,3,'ac4db4521eec0a59d2c098e744e2327b',1),(85,1,'ac4db4521eec0a59d2c098e744e2327b',12),(86,1,'e296b21ed163ef0c8a519bfcbc730ff4',1),(87,2,'e296b21ed163ef0c8a519bfcbc730ff4',1),(88,19,'f0ce56e50168b4892f10b0493cd67d58',1),(89,1,'0ba001a4ae9227d11cd8639411254958',1),(90,7,'767733725566558992604781a392f735',3),(91,1,'9de4a049844e087980d8c2b94b16c2c9',3),(92,2,'2beda0616934a9b3e539aceb3481e589',1),(93,1,'2beda0616934a9b3e539aceb3481e589',3),(94,1,'45b29cfe273451152cc84efc010cf54f',4),(95,14,'5e074377c4ed60c139952046d647d1b8',1),(96,15,'5e074377c4ed60c139952046d647d1b8',1),(97,13,'5e074377c4ed60c139952046d647d1b8',2),(98,12,'5e074377c4ed60c139952046d647d1b8',1),(99,10,'5e074377c4ed60c139952046d647d1b8',1),(100,7,'5e074377c4ed60c139952046d647d1b8',1),(101,8,'5e074377c4ed60c139952046d647d1b8',1),(102,9,'5e074377c4ed60c139952046d647d1b8',1),(103,3,'5e074377c4ed60c139952046d647d1b8',1),(104,2,'5e074377c4ed60c139952046d647d1b8',1),(105,1,'5e074377c4ed60c139952046d647d1b8',1),(106,1,'d84c3307408fbf861e9d489a7828a318',2),(107,2,'80c0371d172d5bc22b482db415544f44',1);
/*!40000 ALTER TABLE `orderitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `price` double NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `status_order` int(11) DEFAULT NULL,
  `oderitem` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E52FFDEE273C268` (`status_order`),
  KEY `IDX_E52FFDEEA76ED395` (`user_id`),
  CONSTRAINT `FK_E52FFDEE273C268` FOREIGN KEY (`status_order`) REFERENCES `status_order` (`id`),
  CONSTRAINT `FK_E52FFDEEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (3,'2017-10-20 11:00:00',482.56,'Московская обл, Павлово-Посадский район, дер Евсеево, дом 10а','qwqwd','8999222222','John','John','2017-10-20 13:34:11',3,'6902dd2762041038bb115f2b6d84b881','borzovandrei45@gmail.com',1),(4,'2017-10-20 13:00:00',545.76,'Москва, д1','qweqwe','8999222222','John','John','2017-10-20 13:52:54',2,'','',1),(5,'2017-10-15 19:00:00',113.54,'Москва, д1','sadas','8999222222','John','John','2017-10-20 19:49:18',1,'','',2),(6,'2017-10-21 11:00:00',233.76,'asd','sdf','das','asd','asd','2017-10-21 11:21:27',3,'','',3),(7,'2017-10-21 11:00:00',233.76,'asd','sdf','das','asd','asd','2017-10-21 11:23:21',4,'','',3),(12,'2017-10-21 22:00:00',0,'Москва, д1','123','8999222222','John','John','2017-10-21 22:29:12',3,'','',3),(13,'2017-10-21 22:30:00',63.2,'Москва, д1','123','8999222222','John','John','2017-10-21 22:30:13',1,'','',3),(14,'2017-10-21 22:00:00',63.2,'wqe','wqe','wqe','w','w','2017-10-21 22:31:22',2,'','',3),(15,'2017-10-21 22:00:00',0,'123','123','123','123','123','2017-10-21 22:45:41',3,'','',3),(16,'2017-10-21 23:00:00',782.92,'123','123','123','123','123','2017-10-21 23:05:24',1,'','',3),(17,'2017-10-21 23:00:00',189.6,'qwe','wqe','qwe','123','qwe','2017-10-21 23:07:26',4,'','',3),(22,'2017-10-25 16:30:00',189.6,'Москва, ул. Первая, д12, кв43','Выберете получше','8(925)123-45-67','John','Doe','2017-10-22 16:31:42',1,'','',3),(23,'2017-10-23 09:00:00',340.62,'Москва, д1','213','8999222222','John','Doe','2017-10-23 08:58:51',2,'','',3),(24,'2017-10-23 17:00:00',126.4,'Москва, д1','das','8999222222','John','Doe','2017-10-23 17:26:45',1,'346c36aa87adef836f57d4d1439a2ef0','',3),(25,'2017-10-23 17:00:00',1872,'Москва, д1','молоко','8999222222','John','Doe','2017-10-23 17:39:15',1,'b3cffdd19afdfd6d4df64a6a518cf89e','',3),(26,'2017-10-23 17:00:00',151.02,'Москва, д1','вишня','8999222222','John','Doe','2017-10-23 17:40:45',1,'90b44d2a681f87d0296316e54bd8af5c','',3),(27,'2017-10-23 17:00:00',50.34,'Москва, д1','Вишня','8999222222','John','Doe','2017-10-23 17:47:21',1,'fcbc2df2295bccc9734b6ced7c2c4911','',3),(28,'2017-10-23 17:00:00',126.4,'Москва, д1','123','8999222222','John','Doe','2017-10-23 17:48:56',1,'c0ebefca219a0d43a8d16ade10eafb0e','',1),(29,'2017-10-23 20:00:00',1052.8,'Москва, д1','мой заказ','8999222222','John','Doe','2017-10-23 20:39:21',4,'071b335d856acb94ff27835823adffca','',1),(30,'2017-10-24 16:00:00',252.8,'qwerty','qwerty','1234567','qwerty','qwerty','2017-10-24 00:06:19',1,'8c0e9179f73a1198bd84db5d7392543f','',1),(31,'2017-10-24 19:00:00',465.34,'Москва, д1','Юлин заказ','8999222222','John','Doe','2017-10-24 08:55:52',1,'c798d27e02385acb3683519bf1763836','',1),(32,'2017-10-24 15:00:00',956.73,'Москва, д1','заказ завтра','8999222222','John','Doe','2017-10-24 15:07:21',3,'ed2631e096548508a07ba8ffb1f96d0b','',1),(33,'2017-10-25 12:00:00',252.8,'Москва, д1','1234567890','8999222222','John','Doe','2017-10-25 12:43:02',1,'11d85457bb18cdbfcd62322885b90e28','',1),(34,'2017-10-27 16:00:00',247.76,'ц','йцу','88005553535','Иванов','Иван','2017-10-27 16:43:35',3,'2fd6f29ed55fbcad76b621c85bbf371e','',3),(35,'2017-10-30 13:00:00',63.2,'Москва, д1','Выберете получше, пожалуйста))','8999222222','John','Doe','2017-10-30 13:46:17',3,'b285e3374629de9545d287d9179789bd','borzovandrei45@gmail.com',2),(36,'2017-10-30 14:00:00',1054.34,'Москва, д1','1','8999222222','John','Doe','2017-10-30 14:55:25',1,'8a5e1d5e3eff7018e1e28aab1b5156fd','borzovandrei45@gmail.com',2),(37,'2017-10-30 14:00:00',312,'Москва, д1','qwe','8999222222','John','Doe','2017-10-30 14:56:38',1,'94115d28b69a94c948e41ecd24d12b1c','john@example.com',2),(38,'2017-10-30 14:00:00',63.2,'Москва, д1','qwe','8999222222','John','Doe','2017-10-30 14:59:07',1,'cd3225f7689d41fba973ef79388974c1','john@example.com',3),(39,'2017-10-30 19:00:00',347.17,'Москва, д1','Заказ Наташи.','8999222222','John','Doe','2017-10-30 19:22:12',2,'919bc7ad69ab0a4dc51ea3b9a6a3f4f6','john@example.com',2),(40,'2017-10-30 19:00:00',113.54,'Москва, д1','Мой заказ','8999222222','John','Doe','2017-10-30 19:23:35',2,'fd28fc8ad65ec94cfa1ff513ca9a9454','borzovandrei45@gmail.com',1),(41,'2017-11-02 15:00:00',272,'Москва, д1','Наташин Никулиной заказ','8999222222','John','Doe','2017-10-31 08:12:18',2,'68357f6c91932fa83c6bd28b7e4dcef4','borzovandrei45@gmail.com',2),(42,'2017-10-31 19:00:00',252.8,'Москва, д1','qwert','8999222222','John','Doe','2017-10-31 19:56:34',1,'73b182e873359ff9463a5a900602e477','john@example.com',2),(43,'2017-10-31 19:00:00',3558.85,'Москва, д1','Рейтинг','8999222222','John','Doe','2017-10-31 19:58:15',1,'49edb0c024813c1938b61e168d0142db','borzovandrei45@gmail.com',2),(44,'2017-10-31 20:00:00',189.6,'Москва, д1','йцу','8999222222','John','Doe','2017-10-31 20:02:23',1,'b851b9b6df579f0a84a16adecb87e825','john@example.com',3),(45,'2017-11-01 11:00:00',63.2,'Московская обл., Павловский посад, ул. Кирова, д12','первый заказ','+79261241493','Андрей','Андрей','2017-11-01 11:17:29',1,'b48d9cdf1327f8a27c8feb1daf4318af','borzovandrei45@gmail.com',3),(46,'2017-11-02 09:00:00',702.39,'Москва','заказ зафара','8999222222','John','Doe','2017-11-02 08:13:29',2,'a1d1cef7561c777975a500dc2774e20f','borzovandrei45@gmail.com',1),(47,'2017-11-14 11:15:00',126.4,'Московская обл., Павловский посад, ул. Кирова, д12','тест','+79261241493','Андрей','Андрей','2017-11-14 11:15:28',1,'c993792f3a11b142904abd3da2c62d0c','borzovandrei45@gmail.com',2),(48,'2017-11-14 11:00:00',63.2,'Московская обл., Павловский посад, ул. Кирова, д12','1','79261241493','Андрей','Андрей','2017-11-14 11:16:40',1,'eeb90d3f11bc714e5c4bef32773009f8','borzovandrei45@gmail.com',3),(49,'2017-12-12 18:42:54',10,'Москва, д1','Заказанно через telegram','8999222222','John','Doe','2017-12-12 18:42:54',1,'60cf804690525563755d3b6326e1d21b','john@example.com',3),(56,'2017-12-12 20:10:10',10,'Москва, д1','Заказанно через telegram','8999222222','John','Doe','2017-12-12 20:10:10',1,'614c071cebf84d4fa8aaffc7375bb72a','john@example.com',3),(57,'2017-12-12 20:20:05',758.3,'Москва, д1','Заказанно через telegram','8999222222','John','Doe','2017-12-12 20:20:05',1,'7163b95a89b7b5fcc29a27926b29cc3d','john@example.com',3),(58,'2017-12-12 20:25:15',284.1,'Москва, д1','Заказанно через telegram','8999222222','John','Doe','2017-12-12 20:25:15',1,'a8df5f01c87b7b825c31b5af590f5555','john@example.com',1),(59,'2017-12-12 21:15:12',979.3,'Москва, д1','Заказанно через telegram','8999222222','John','Doe','2017-12-12 21:15:12',1,'ac4db4521eec0a59d2c098e744e2327b','john@example.com',3),(60,'2017-12-13 11:59:46',113.54,'Москва, д1','Заказанно через telegram','8999222222','John','Doe','2017-12-13 11:59:46',1,'e296b21ed163ef0c8a519bfcbc730ff4','john@example.com',3),(61,'2017-12-25 08:47:16',87,'','Заказанно через telegram','88005553535','Иванов','Иван','2017-12-25 08:47:16',1,'f0ce56e50168b4892f10b0493cd67d58','qwerty@gm.com',1),(62,'2018-01-18 16:21:08',63.2,'','Заказанно через telegram','88005553535','Иванов','Иван','2018-01-18 16:21:08',1,'0ba001a4ae9227d11cd8639411254958','qwerty@gm.com',2),(63,'2018-02-20 15:00:00',189.6,'Московская обл., Павловский посад, ул. Кирова, д12','1','+79261241493','Андрей','Андрей','2018-02-20 15:36:36',4,'9de4a049844e087980d8c2b94b16c2c9','borzovandrei45@gmail.com',3),(64,'2018-02-20 15:00:00',239.94,'Московская обл., Павловский посад, ул. Кирова, д12','12','+79261241493','Андрей','Андрей','2018-02-20 15:38:40',3,'2beda0616934a9b3e539aceb3481e589','borzovandrei45@gmail.com',3),(65,'2018-02-20 15:00:00',252.8,'23','123','22323','12','12','2018-02-20 15:41:28',1,'45b29cfe273451152cc84efc010cf54f','qwe@erda.qw',NULL),(66,'2018-02-20 17:00:00',1280.63,'Московская обл., Павловский посад, ул. Кирова, д12','123','+79261241491','Андрей','Андрей','2018-02-20 17:37:40',1,'5e074377c4ed60c139952046d647d1b8','borzovandrei45@gmail.com',3),(67,'2018-02-21 11:00:00',126.4,'Московская обл., Павловский посад, ул. Кирова, д123','проба','+79261241491','Андрей','Андрей','2018-02-21 11:12:56',1,'d84c3307408fbf861e9d489a7828a318','borzovandrei45@gmail.com',3),(68,'2018-02-21 14:00:00',50.34,'Московская обл., Павловский посад, ул. Кирова, д123','12','+79261241491','Андрей','Андрей','2018-02-21 14:58:13',1,'80c0371d172d5bc22b482db415544f44','borzovandrei45@gmail.com',3);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_id` int(11) DEFAULT NULL,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `shop_price` double NOT NULL,
  `balanse` double NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `measure_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `telegram` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B3BA5A5A47013001` (`sort_id`),
  KEY `IDX_B3BA5A5A5DA37D00` (`measure_id`),
  CONSTRAINT `FK_B3BA5A5A47013001` FOREIGN KEY (`sort_id`) REFERENCES `sort` (`id`),
  CONSTRAINT `FK_B3BA5A5A5DA37D00` FOREIGN KEY (`measure_id`) REFERENCES `measure` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Granny Smith',42.41,63.2,1698.1,'apple-1.png',1,51,'CAADAgADAgADoGJ4AbxAkLTRPcsXAg'),(2,1,'Спелая вишня',14.2,50.34,126.5,'cherries.png',1,13,'CAADAgADCgADoGJ4AQABUkSEB6qVEAI'),(3,1,'Клубника',42.11,120.22,427.53,'strawberry.png',1,2,'CAADAgADDwADoGJ4AVyd7e1bpqfdAg'),(7,2,'Молоко',21.21,312,3241,'milk-1.png',2,6,''),(8,3,'Мясо',45,68,575,'meat-1.png',1,4,''),(9,9,'Хлеб',10,14,2815,'bread.png',1,1,''),(10,4,'Рыба',100,234.13,187,'fish.png',1,5,''),(12,3,'Бургер',12,123,227,'hamburguer.png',4,1,''),(13,3,'Кебаб',21,123,198,'kebab.png',4,6,''),(14,1,'Виноград',23,34.4,86,'grapes.png',1,8,''),(15,1,'Банан',12,15.34,42,'banana.png',1,6,''),(16,4,'Креветки',150,194.23,563,'shrimp.png',1,6,''),(17,8,'Лимонад',50,65.23,6833,'jelly.png',2,8,''),(18,9,'Хлеб черный',9,12,123,'bread-1.png',4,0,''),(19,2,'Молоко \"Домашнее\"',64,87,449,'milk.png',2,1,'');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'ROLE_ADMIN','2017-10-10 19:12:30'),(2,'ROLE_USER','2017-10-12 00:00:00'),(3,'ROLE_MANAGER','2017-11-01 10:23:37');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sex`
--

DROP TABLE IF EXISTS `sex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sex` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sex`
--

LOCK TABLES `sex` WRITE;
/*!40000 ALTER TABLE `sex` DISABLE KEYS */;
INSERT INTO `sex` VALUES (1,'Мужчина'),(2,'Девушка');
/*!40000 ALTER TABLE `sex` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sort`
--

DROP TABLE IF EXISTS `sort`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sort` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `about` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sort`
--

LOCK TABLES `sort` WRITE;
/*!40000 ALTER TABLE `sort` DISABLE KEYS */;
INSERT INTO `sort` VALUES (1,'Овощи и фрукты','Продукты'),(2,'Молочные продукты','Продукты'),(3,'Мясо','Продукты'),(4,'Рыба и морепродукты','Продукты'),(5,'Колбасы','Продукты'),(6,'Крупы и бакалея','Продукты'),(7,'Консервация','Продукты'),(8,'Напитки','Продукты'),(9,'Хлеб и кондитерские изделия','Продукты'),(10,'Замороженные блюда','Продукты'),(11,'Алкогольные напитки','Продукты'),(12,'Для детей и мам','Продукты'),(13,'Чистота и порядок','Продукты');
/*!40000 ALTER TABLE `sort` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status_order`
--

DROP TABLE IF EXISTS `status_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status_order`
--

LOCK TABLES `status_order` WRITE;
/*!40000 ALTER TABLE `status_order` DISABLE KEYS */;
INSERT INTO `status_order` VALUES (1,'В обработке'),(2,'Выполняется'),(3,'Выполнен'),(4,'Возврат');
/*!40000 ALTER TABLE `status_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `age` date NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sex_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  KEY `IDX_8D93D6495A2DB2A0` (`sex_id`),
  CONSTRAINT `FK_8D93D6495A2DB2A0` FOREIGN KEY (`sex_id`) REFERENCES `sex` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'john.doe','John','Doe','john@example.com','1991-04-12','/UZBtLgi+zwrzVVTPjvDLDXYcC4E2MLFhIv8vAsydfoC1ytcgkcfT9idq/n7fJqe/mkm7ecoEFDgjPxNqiI8eA==','52ac473f2e91a1453f08e5a876b283aa','8999222222','Москва, д12','boy-14.svg',1),(2,'qwerty','Иванов','Иван','qwerty@gm.com','2007-10-02','/UZBtLgi+zwrzVVTPjvDLDXYcC4E2MLFhIv8vAsydfoC1ytcgkcfT9idq/n7fJqe/mkm7ecoEFDgjPxNqiI8eA==','52ac473f2e91a1453f08e5a876b283aa','88005553535','','boy-14.svg',2),(3,'user','Андрей','Андрей','borzovandrei45@gmail.com','2012-07-06','HGrXvLcHvcox66Aj1w9Iz9OabPTPIxYK9zngh3mow5z84SBpTYOsfET+d8B1L0+Va4YAmCj3SxaMQZbTSJPF0A==','87634aa56ff08b8079167cb6c110de8c','+79261241491','Московская обл., Павловский посад, ул. Кирова, д123','boy-14.svg',1),(4,'Иван','ифыв','ыва','borzovandrei435@gmail.com','2012-01-01','WQg9nRcnNYtwtCa99dU1YfvnLdJqW48C7GzBsUe/PLVDf9SGG+TSwmFokt6T9OQlElQ2n1XD+Lm8/Dj4zolRMA==','6b055ebca8208c1e5d972e818756e7fc','+7312','dsf','opengraph_thumb.jpg',1);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_2DE8C6A3A76ED395` (`user_id`),
  KEY `IDX_2DE8C6A3D60322AC` (`role_id`),
  CONSTRAINT `FK_2DE8C6A3A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_2DE8C6A3D60322AC` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_role`
--

LOCK TABLES `user_role` WRITE;
/*!40000 ALTER TABLE `user_role` DISABLE KEYS */;
INSERT INTO `user_role` VALUES (1,1),(1,2),(2,2),(2,3),(3,2),(4,2);
/*!40000 ALTER TABLE `user_role` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-02-22 13:48:38
