-- MySQL dump 10.13  Distrib 5.1.63, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: banweb_test_main
-- ------------------------------------------------------
-- Server version	5.1.63-0+squeeze1

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
-- Table structure for table `access_log`
--

DROP TABLE IF EXISTS `access_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `initial_access` datetime NOT NULL,
  `num_successes` int(11) NOT NULL,
  `num_failures` int(11) NOT NULL,
  `num_reset_attempts` int(11) NOT NULL,
  `reset_key` varchar(255) NOT NULL,
  `reset_expiration` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_log`
--

LOCK TABLES `access_log` WRITE;
/*!40000 ALTER TABLE `access_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `access_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accesses`
--

DROP TABLE IF EXISTS `accesses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accesses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accesses`
--

LOCK TABLES `accesses` WRITE;
/*!40000 ALTER TABLE `accesses` DISABLE KEYS */;
INSERT INTO `accesses` VALUES (1,1,'accesses',''),(2,1,'users',''),(3,2,'create','users'),(4,2,'modify','users'),(5,2,'delete','users'),(7,3,'password','modify');
/*!40000 ALTER TABLE `accesses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blacklist_preferences`
--

DROP TABLE IF EXISTS `blacklist_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blacklist_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `classes` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blacklist_preferences`
--

LOCK TABLES `blacklist_preferences` WRITE;
/*!40000 ALTER TABLE `blacklist_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `blacklist_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semester_blacklist`
--

DROP TABLE IF EXISTS `semester_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `semester_blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_submitted` datetime NOT NULL,
  `year` varchar(255) NOT NULL,
  `semester` varchar(255) NOT NULL,
  `json` text NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semester_blacklist`
--

LOCK TABLES `semester_blacklist` WRITE;
/*!40000 ALTER TABLE `semester_blacklist` DISABLE KEYS */;
INSERT INTO `semester_blacklist` VALUES (1,'2013-03-07 12:46:32','2013','30','[]',1),(2,'2013-09-26 14:31:44','2014','20','[[10,\"contains\",\"Hamdy\",{\"matchedCourses\":14}]]',2);
/*!40000 ALTER TABLE `semester_blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semester_classes`
--

DROP TABLE IF EXISTS `semester_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `semester_classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_submitted` datetime NOT NULL,
  `year` varchar(255) NOT NULL,
  `semester` varchar(255) NOT NULL,
  `json` text NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `studentid` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semester_classes`
--

LOCK TABLES `semester_classes` WRITE;
/*!40000 ALTER TABLE `semester_classes` DISABLE KEYS */;
INSERT INTO `semester_classes` VALUES (1,'2013-03-07 13:42:18','2013','30','[{\"crn\":62830},{\"crn\":62673},{\"crn\":63042},{\"crn\":62829},{\"crn\":62824},{\"crn\":62167},{\"crn\":62825}]',1),(3,'2013-03-07 17:03:30','2014','20','[{\"crn\":54783}]',1);
/*!40000 ALTER TABLE `semester_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semester_whitelist`
--

DROP TABLE IF EXISTS `semester_whitelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `semester_whitelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_submitted` datetime NOT NULL,
  `year` varchar(255) NOT NULL,
  `semester` varchar(255) NOT NULL,
  `json` text NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semester_whitelist`
--

LOCK TABLES `semester_whitelist` WRITE;
/*!40000 ALTER TABLE `semester_whitelist` DISABLE KEYS */;
INSERT INTO `semester_whitelist` VALUES (1,'2013-03-07 04:20:18','2013','30','[]',1);
/*!40000 ALTER TABLE `semester_whitelist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `pass` varchar(100) NOT NULL,
  `accesses` varchar(100) NOT NULL,
  `email` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'benjamin','LÞø.tÆÔ4€!q9Z©','users|accesses|feedback|custom','bbean@cs.nmt.edu'),(2,'guest','É\"A°®èº“-·[ð{×','feedback',''),(4,'thedude','Ìwn|ÅP‰ø¶à×ÀI','feedback','asdf');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tabs`
--

DROP TABLE IF EXISTS `tabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tabs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `_deleted` tinyint(1) NOT NULL,
  `accesses` varchar(255) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tabs`
--

LOCK TABLES `tabs` WRITE;
/*!40000 ALTER TABLE `tabs` DISABLE KEYS */;
INSERT INTO `tabs` VALUES (1,'Schedule',0,'',1),(2,'Classes',0,'',2),(3,'Lists',0,'',3),(4,'Settings',0,'',4),(5,'Feedback',0,'',5),(6,'Users',0,'users',6),(7,'Custom',0,'',0);
/*!40000 ALTER TABLE `tabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_settings`
--

DROP TABLE IF EXISTS `user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_timeout` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_settings`
--

LOCK TABLES `user_settings` WRITE;
/*!40000 ALTER TABLE `user_settings` DISABLE KEYS */;
INSERT INTO `user_settings` VALUES (1,1,-1,'server');
/*!40000 ALTER TABLE `user_settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-10-18 21:20:02
