-- MySQL dump 10.13  Distrib 5.5.40, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: glass
-- ------------------------------------------------------
-- Server version	5.5.40-0ubuntu0.12.04.1

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
-- Table structure for table `localreports`
--

DROP TABLE IF EXISTS `localreports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `localreports` (
  `uniq` int(10) NOT NULL AUTO_INCREMENT,
  `itemgroup` varchar(20) DEFAULT 'lookup',
  `itemid` varchar(20) DEFAULT 'uniqid',
  `itemname` varchar(60) DEFAULT '',
  `itemdesc` text,
  `seclevel` int(6) DEFAULT '90',
  `availtologins` text,
  `availtoroles` text,
  `query` text,
  `query2` text,
  `groupon` text,
  `asnumbers` text NOT NULL,
  `totals` text,
  `input1field` text,
  `input1source` varchar(80) DEFAULT '',
  `input2field` text,
  `input2source` varchar(80) DEFAULT '',
  `input3field` text,
  `input3source` varchar(80) DEFAULT '',
  `input4field` text,
  `input4source` varchar(80) DEFAULT '',
  `input5field` text,
  `input5source` varchar(80) DEFAULT '',
  `input6field` text,
  `input6source` varchar(80) DEFAULT '',
  `input7field` text,
  `input7source` varchar(80) DEFAULT '',
  `input8field` text,
  `input8source` varchar(80) DEFAULT '',
  `input9field` text,
  `input9source` varchar(80) DEFAULT '',
  `fieldcharlimit` int(6) DEFAULT '40',
  `defaultdatemode` varchar(10) DEFAULT 'D',
  `output` varchar(20) DEFAULT 'REPORT',
  `outputformats` varchar(80) DEFAULT 'HTML|CSV|XLS|PDF',
  `outputmode` varchar(80) DEFAULT 'NOW|OFFLINE|EMAIL',
  `counter` int(10) DEFAULT '0',
  `comments` text,
  `created` date NOT NULL DEFAULT '0000-00-00',
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `displaynotes` text,
  `groupchart` text,
  `totalchart` text,
  `mapchart` text,
  `maxtime` double(16,4) DEFAULT '0.0000',
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  UNIQUE KEY `itemid` (`itemid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `localreports`
--

LOCK TABLES `localreports` WRITE;
/*!40000 ALTER TABLE `localreports` DISABLE KEYS */;
/*!40000 ALTER TABLE `localreports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people` (
  `uniq` bigint(20) NOT NULL AUTO_INCREMENT,
  `login` varchar(20) NOT NULL DEFAULT 'invalid',
  `passwd` varchar(80) NOT NULL DEFAULT '87654321ABCDEF',
  `level` int(5) NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL DEFAULT '',
  `content` text,
  `integer` int(10) NOT NULL DEFAULT '0',
  `double` double(16,2) NOT NULL DEFAULT '0.00',
  `decimal` decimal(16,2) NOT NULL DEFAULT '0.00',
  `bit` bit(8) NOT NULL DEFAULT b'0',
  `point` point DEFAULT NULL,
  `polygon` polygon DEFAULT NULL,
  `dateexample` date NOT NULL DEFAULT '0000-00-00',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email` varchar(200) DEFAULT '',
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `people`
--

LOCK TABLES `people` WRITE;
/*!40000 ALTER TABLE `people` DISABLE KEYS */;
INSERT INTO `people` VALUES (1,'Mike','1234',99,'Mike Harrison',NULL,0,0.00,0.00,'\0',NULL,NULL,'0000-00-00','2014-11-17 17:10:11','2014-11-17 15:04:19','');
/*!40000 ALTER TABLE `people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportq`
--

DROP TABLE IF EXISTS `reportq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportq` (
  `uniq` int(10) NOT NULL AUTO_INCREMENT,
  `portal` varchar(20) NOT NULL DEFAULT 'invalid',
  `login` varchar(20) NOT NULL DEFAULT 'invalid',
  `itemid` varchar(20) NOT NULL DEFAULT 'invalid',
  `request` text,
  `requestdatetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completedatetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  KEY `portal` (`portal`) USING BTREE,
  KEY `requestdatetime` (`requestdatetime`) USING BTREE,
  KEY `login` (`login`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportq`
--

LOCK TABLES `reportq` WRITE;
/*!40000 ALTER TABLE `reportq` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `uniq` int(10) NOT NULL AUTO_INCREMENT,
  `itemgroup` varchar(20) DEFAULT 'lookup',
  `itemid` varchar(20) DEFAULT 'uniqid',
  `itemname` varchar(60) DEFAULT '',
  `itemdesc` text,
  `seclevel` int(6) DEFAULT '90',
  `availtologins` text,
  `availtoroles` text,
  `query` text,
  `query2` text,
  `groupon` text,
  `asnumbers` text NOT NULL,
  `totals` text,
  `input1field` text,
  `input1source` varchar(80) DEFAULT '',
  `input2field` text,
  `input2source` varchar(80) DEFAULT '',
  `input3field` text,
  `input3source` varchar(80) DEFAULT '',
  `input4field` text,
  `input4source` varchar(80) DEFAULT '',
  `input5field` text,
  `input5source` varchar(80) DEFAULT '',
  `input6field` text,
  `input6source` varchar(80) DEFAULT '',
  `input7field` text,
  `input7source` varchar(80) DEFAULT '',
  `input8field` text,
  `input8source` varchar(80) DEFAULT '',
  `input9field` text,
  `input9source` varchar(80) DEFAULT '',
  `output` varchar(20) DEFAULT 'REPORT',
  `outputformats` varchar(80) DEFAULT 'HTML|CSV|XLS|PDF',
  `outputmode` varchar(80) DEFAULT 'NOW|OFFLINE|EMAIL',
  `groupchart` text,
  `totalchart` text,
  `mapchart` text,
  `displaynotes` text,
  `comments` text,
  `defaultdatemode` varchar(10) DEFAULT 'D',
  `fieldcharlimit` int(6) DEFAULT '40',
  `maxtime` double(16,4) DEFAULT '0.0000',
  `counter` int(10) DEFAULT '0',
  `created` date NOT NULL DEFAULT '0000-00-00',
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  UNIQUE KEY `itemid` (`itemid`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (10,'lookup','FirstReport','People Report',NULL,90,'','','select * from people where login = &apos;input1&apos; and created >= &#39;fromdate&#39; and created < &#39;todate&#39; ',NULL,NULL,'integer,double|decimal|bit,level',NULL,'Login','',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'','REPORT','HTML|CSV|XLS|PDF','NOW|OFFLINE|EMAIL',NULL,NULL,NULL,NULL,NULL,'D',40,0.0000,0,'2014-11-17','2014-11-17 16:12:14'),(30,'lookup','allpeople','allpeople report',NULL,90,'','','select * from people where login = &apos;input1&apos; and created >= &#39;fromdate&#39; and created < &#39;todate&#39; ',NULL,NULL,'integer,double|decimal|bit,level',NULL,'Login','',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'','REPORT','HTML|CSV|XLS|PDF','NOW|OFFLINE|EMAIL',NULL,NULL,NULL,NULL,NULL,'D',40,0.0058,66,'2014-11-17','2014-11-17 21:08:43');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-17 13:11:03
