CREATE DATABASE  IF NOT EXISTS `accountsdb` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `accountsdb`;
-- MySQL dump 10.13  Distrib 5.6.17, for Win32 (x86)
--
-- Host: 172.16.102.157    Database: accountsdb
-- ------------------------------------------------------
-- Server version	5.6.14-enterprise-commercial-advanced-log

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
-- Table structure for table `accountdetails`
--

DROP TABLE IF EXISTS `accountdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountdetails` (
  `AID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyName` varchar(150) DEFAULT NULL,
  `CompanyAddress` varchar(150) DEFAULT NULL,
  `CompanyEmail` varchar(100) DEFAULT NULL,
  `CompanyLandline` varchar(20) DEFAULT NULL,
  `CompanyMobileNumber` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`AID`),
  CONSTRAINT `FK_accountdetails_accounts_AID` FOREIGN KEY (`AID`) REFERENCES `accounts` (`AID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountdetails`
--

LOCK TABLES `accountdetails` WRITE;
/*!40000 ALTER TABLE `accountdetails` DISABLE KEYS */;
INSERT INTO `accountdetails` VALUES (1,'PW','OSMA','mccalderon@philweb.com.ph','33385599','09999999999');
/*!40000 ALTER TABLE `accountdetails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `AID` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(20) DEFAULT NULL,
  `Password` varchar(50) DEFAULT NULL,
  `DateLastLogin` datetime(6) DEFAULT NULL,
  `LoginAttempts` tinyint(4) DEFAULT NULL,
  `DateCreated` datetime(6) DEFAULT NULL,
  `CreatedByAID` int(11) DEFAULT NULL,
  `ForChangePassword` tinyint(4) DEFAULT NULL,
  `Status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`AID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,'tpadmintere','523f20a3b5142c115944db9b9289a70319693c40',NULL,NULL,'2014-07-22 11:02:50.668057',1,NULL,1),(2,'tpadminjem','5f4dcc3b5aa765d61d8327deb882cf99',NULL,NULL,'2014-07-23 11:02:50.668057',1,NULL,1);
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accountsessions`
--

DROP TABLE IF EXISTS `accountsessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountsessions` (
  `AID` int(11) NOT NULL AUTO_INCREMENT,
  `SessionID` varchar(50) DEFAULT NULL,
  `DateCreated` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`AID`),
  CONSTRAINT `FK_accountsessions_accounts_AID` FOREIGN KEY (`AID`) REFERENCES `accounts` (`AID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountsessions`
--

LOCK TABLES `accountsessions` WRITE;
/*!40000 ALTER TABLE `accountsessions` DISABLE KEYS */;
INSERT INTO `accountsessions` VALUES (1,'r528v1v4j4695jc8a3jaaa7sr4','2014-07-23 19:16:39.013815'),(2,'msu81dc6bbsud2hrudl7goj3k4','2014-07-24 17:29:22.257662'),(3,'2i6rgbb353lkn2mfv8dq006p31','2014-07-23 19:35:22.614230');
/*!40000 ALTER TABLE `accountsessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apilogs`
--

DROP TABLE IF EXISTS `apilogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apilogs` (
  `LogID` bigint(20) NOT NULL AUTO_INCREMENT,
  `ApiMethodID` int(11) DEFAULT NULL,
  `ReferenceID` int(11) DEFAULT NULL,
  `Transdetails` varchar(255) DEFAULT NULL,
  `DateLastUpdated` datetime(6) DEFAULT NULL,
  `TrackingID` varchar(50) DEFAULT NULL,
  `RemoteIP` varchar(50) DEFAULT NULL,
  `Status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`LogID`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apilogs`
--

LOCK TABLES `apilogs` WRITE;
/*!40000 ALTER TABLE `apilogs` DISABLE KEYS */;
INSERT INTO `apilogs` VALUES (1,9,0,'Successfull','2014-07-24 11:59:40.816439','','127.0.0.1',2),(2,9,0,'No Error, Transaction successful.','2014-07-24 13:08:51.930935','','::1',1),(3,9,0,'No Error, Transaction successful.','2014-07-24 13:09:42.862429','','::1',1),(4,9,0,'No Error, Transaction successful.','2014-07-24 13:58:06.089522','','::1',1),(5,9,0,'No Error, Transaction successful.','2014-07-24 13:58:12.058470','','::1',1),(6,9,0,'No Error, Transaction successful.','2014-07-24 14:02:19.568706','','::1',1),(7,9,0,'No Error, Transaction successful.','2014-07-24 14:03:06.127098','','::1',1),(8,9,0,'No Error, Transaction successful.','2014-07-24 14:10:26.603097','','::1',1),(9,9,0,'No Error, Transaction successful.','2014-07-24 14:12:14.754643','','::1',1),(10,9,0,'No Error, Transaction successful.','2014-07-24 14:15:32.470427','','::1',1),(11,9,0,'No Error, Transaction successful.','2014-07-24 14:21:06.316404','','::1',1),(12,9,0,'No Error, Transaction successful.','2014-07-24 14:23:29.141464','','::1',1),(13,9,0,'No Error, Transaction successful.','2014-07-24 14:25:54.449608','','::1',1),(14,9,0,'No Error, Transaction successful.','2014-07-24 14:30:10.587089','','::1',1),(15,9,0,'No Error, Transaction successful.','2014-07-24 14:32:17.588675','','::1',1),(16,9,0,'No Error, Transaction successful.','2014-07-24 14:40:26.837437','','::1',1),(17,9,0,'No Error, Transaction successful.','2014-07-24 15:13:12.808826','','::1',1),(18,9,0,'Successfull','2014-07-24 15:13:31.695131','','127.0.0.1',2),(19,9,0,'No Error, Transaction successful.','2014-07-24 15:15:56.746365','','::1',1),(20,9,0,'No Error, Transaction successful.','2014-07-24 15:25:10.686079','','::1',1),(21,9,0,'No Error, Transaction successful.','2014-07-24 16:04:45.321590','','::1',1),(22,9,0,'No Error, Transaction successful.','2014-07-24 17:24:35.022182','','::1',1),(23,9,0,'No Error, Transaction successful.','2014-07-24 17:27:30.396078','','::1',1),(24,9,0,'No Error, Transaction successful.','2014-07-24 17:29:22.275647','','::1',1);
/*!40000 ALTER TABLE `apilogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audittrail`
--

DROP TABLE IF EXISTS `audittrail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audittrail` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `SessionID` int(11) DEFAULT NULL,
  `AID` int(11) DEFAULT NULL,
  `Transdetails` varchar(255) DEFAULT NULL,
  `TransDate` datetime(6) DEFAULT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `RemoteIP` varchar(50) DEFAULT NULL,
  `AuditTrailFunctionID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `FK_audittrail_ref_auditfunctions_AuditTrailFunctionID` (`AuditTrailFunctionID`),
  CONSTRAINT `FK_audittrail_ref_auditfunctions_AuditTrailFunctionID` FOREIGN KEY (`AuditTrailFunctionID`) REFERENCES `ref_auditfunctions` (`AuditTrailFunctionID`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audittrail`
--

LOCK TABLES `audittrail` WRITE;
/*!40000 ALTER TABLE `audittrail` DISABLE KEYS */;
INSERT INTO `audittrail` VALUES (1,0,2,'No Error, Transaction successful.','2014-07-24 10:40:52.787495','2014-07-24 02:40:53','JDLACHICA.corp.philweb.local',9),(2,0,2,'No Error, Transaction successful.','2014-07-24 10:45:31.116907','2014-07-24 02:45:31','JDLACHICA.corp.philweb.local',9),(3,0,2,'No Error, Transaction successful.','2014-07-24 11:10:02.356692','2014-07-24 03:10:02','JDLACHICA.corp.philweb.local',9),(4,0,2,'No Error, Transaction successful.','2014-07-24 11:13:26.346247','2014-07-24 03:13:26','JDLACHICA.corp.philweb.local',9),(5,70000000,2,'No Error, Transaction successful.','2014-07-24 11:23:59.264696','2014-07-24 03:23:59','JDLACHICA.corp.philweb.local',9),(6,0,2,'No Error, Transaction successful.','2014-07-24 11:24:23.565485','2014-07-24 03:24:24','JDLACHICA.corp.philweb.local',9),(7,52,2,'No Error, Transaction successful.','2014-07-24 11:32:47.224393','2014-07-24 03:32:47','JDLACHICA.corp.philweb.local',9),(8,3,2,'No Error, Transaction successful.','2014-07-24 11:34:43.340005','2014-07-24 03:34:43','JDLACHICA.corp.philweb.local',9),(9,0,2,'No Error, Transaction successful.','2014-07-24 11:40:35.829336','2014-07-24 03:40:36','JDLACHICA.corp.philweb.local',9),(10,0,2,'No Error, Transaction successful.','2014-07-24 11:49:40.230349','2014-07-24 03:49:40','JDLACHICA.corp.philweb.local',9),(11,0,2,'No Error, Transaction successful.','2014-07-24 11:51:10.914828','2014-07-24 03:51:11','JDLACHICA.corp.philweb.local',9),(12,8,2,'No Error, Transaction successful.','2014-07-24 11:54:26.155034','2014-07-24 03:54:26','JDLACHICA.corp.philweb.local',9),(13,0,2,'No Error, Transaction successful.','2014-07-24 11:57:48.986677','2014-07-24 03:57:49','JDLACHICA.corp.philweb.local',9),(14,0,2,'No Error, Transaction successful.','2014-07-24 12:01:57.659733','2014-07-24 04:01:58','JDLACHICA.corp.philweb.local',9),(15,0,2,'No Error, Transaction successful.','2014-07-24 12:02:39.087362','2014-07-24 04:02:39','JDLACHICA.corp.philweb.local',9),(16,0,2,'No Error, Transaction successful.','2014-07-24 13:08:06.276629','2014-07-24 05:08:06','JDLACHICA.corp.philweb.local',9),(17,4,2,'No Error, Transaction successful.','2014-07-24 13:08:51.921535','2014-07-24 05:08:52','JDLACHICA.corp.philweb.local',9),(18,0,2,'No Error, Transaction successful.','2014-07-24 13:09:42.854722','2014-07-24 05:09:43','JDLACHICA.corp.philweb.local',9),(19,0,2,'No Error, Transaction successful.','2014-07-24 13:58:06.004038','2014-07-24 05:58:06','JDLACHICA.corp.philweb.local',9),(20,0,2,'No Error, Transaction successful.','2014-07-24 13:58:12.050533','2014-07-24 05:58:12','JDLACHICA.corp.philweb.local',9),(21,0,2,'No Error, Transaction successful.','2014-07-24 14:02:19.558775','2014-07-24 06:02:20','JDLACHICA.corp.philweb.local',9),(22,4,2,'No Error, Transaction successful.','2014-07-24 14:03:06.119164','2014-07-24 06:03:06','JDLACHICA.corp.philweb.local',9),(23,0,2,'No Error, Transaction successful.','2014-07-24 14:10:26.594486','2014-07-24 06:10:27','JDLACHICA.corp.philweb.local',9),(24,0,2,'No Error, Transaction successful.','2014-07-24 14:12:14.746148','2014-07-24 06:12:15','JDLACHICA.corp.philweb.local',9),(25,5,2,'No Error, Transaction successful.','2014-07-24 14:15:32.461207','2014-07-24 06:15:32','JDLACHICA.corp.philweb.local',9),(26,0,2,'No Error, Transaction successful.','2014-07-24 14:21:06.054993','2014-07-24 06:21:06','JDLACHICA.corp.philweb.local',9),(27,0,2,'No Error, Transaction successful.','2014-07-24 14:23:28.839625','2014-07-24 06:23:29','JDLACHICA.corp.philweb.local',9),(28,0,2,'No Error, Transaction successful.','2014-07-24 14:25:54.139526','2014-07-24 06:25:54','JDLACHICA.corp.philweb.local',9),(29,0,2,'No Error, Transaction successful.','2014-07-24 14:30:10.569794','2014-07-24 06:30:11','JDLACHICA.corp.philweb.local',9),(30,78,2,'No Error, Transaction successful.','2014-07-24 14:31:23.763304','2014-07-24 06:31:24','JDLACHICA.corp.philweb.local',9),(31,0,2,'No Error, Transaction successful.','2014-07-24 14:32:17.571300','2014-07-24 06:32:18','JDLACHICA.corp.philweb.local',9),(32,0,2,'No Error, Transaction successful.','2014-07-24 14:40:26.828329','2014-07-24 06:40:27','JDLACHICA.corp.philweb.local',9),(33,0,2,'No Error, Transaction successful.','2014-07-24 15:13:12.798840','2014-07-24 07:13:13','JDLACHICA.corp.philweb.local',9),(34,5,2,'No Error, Transaction successful.','2014-07-24 15:15:56.737899','2014-07-24 07:15:57','JDLACHICA.corp.philweb.local',9),(35,341,2,'No Error, Transaction successful.','2014-07-24 15:25:10.677624','2014-07-24 07:25:11','JDLACHICA.corp.philweb.local',9),(36,0,2,'No Error, Transaction successful.','2014-07-24 16:04:45.312273','2014-07-24 08:04:45','JDLACHICA.corp.philweb.local',9),(37,8,2,'No Error, Transaction successful.','2014-07-24 17:24:35.012956','2014-07-24 09:24:35','JDLACHICA.corp.philweb.local',9),(38,0,2,'No Error, Transaction successful.','2014-07-24 17:27:30.387698','2014-07-24 09:27:30','JDLACHICA.corp.philweb.local',9),(39,0,2,'No Error, Transaction successful.','2014-07-24 17:29:22.267182','2014-07-24 09:29:22','JDLACHICA.corp.philweb.local',9);
/*!40000 ALTER TABLE `audittrail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_apimethod`
--

DROP TABLE IF EXISTS `ref_apimethod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_apimethod` (
  `APIMethodID` int(11) NOT NULL AUTO_INCREMENT,
  `APIMethodName` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`APIMethodID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_apimethod`
--

LOCK TABLES `ref_apimethod` WRITE;
/*!40000 ALTER TABLE `ref_apimethod` DISABLE KEYS */;
INSERT INTO `ref_apimethod` VALUES (1,'Login'),(2,'Forgot Password'),(3,'Register Member'),(4,'Update Profile'),(5,'Get Profile'),(6,'Check Points'),(7,'List Items'),(8,'Redeem Items'),(9,'Authenticate Session');
/*!40000 ALTER TABLE `ref_apimethod` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_auditfunctions`
--

DROP TABLE IF EXISTS `ref_auditfunctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_auditfunctions` (
  `AuditTrailFunctionID` int(11) NOT NULL AUTO_INCREMENT,
  `AuditFunctionName` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`AuditTrailFunctionID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_auditfunctions`
--

LOCK TABLES `ref_auditfunctions` WRITE;
/*!40000 ALTER TABLE `ref_auditfunctions` DISABLE KEYS */;
INSERT INTO `ref_auditfunctions` VALUES (1,'Login'),(2,'Forgot Password'),(3,'Register Member'),(4,'Update Profile'),(5,'Get Profile'),(6,'Check Points'),(7,'List Items'),(8,'Redeem Items'),(9,'Authenticate Session');
/*!40000 ALTER TABLE `ref_auditfunctions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-07-24 19:25:06
