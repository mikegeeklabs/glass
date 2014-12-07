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
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `uniq` bigint(20) NOT NULL AUTO_INCREMENT,
  `account` varchar(20) NOT NULL DEFAULT '',
  `passwd` varchar(20) NOT NULL DEFAULT '',
  `seclevel` int(6) NOT NULL DEFAULT '5',
  `name` varchar(120) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `address1` varchar(80) DEFAULT NULL,
  `address2` varchar(80) DEFAULT NULL,
  `address3` varchar(80) DEFAULT NULL,
  `city` varchar(20) DEFAULT NULL,
  `state` varchar(20) DEFAULT NULL,
  `postalcode` varchar(20) DEFAULT NULL,
  `countrycode` varchar(10) DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastlogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fromip` varchar(20) NOT NULL DEFAULT '127.0.0.2',
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  KEY `account` (`account`) USING BTREE,
  KEY `state` (`state`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`state`) REFERENCES `state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (3,'test123','aasasa',88,'MIKE HARRISON','flloozz','mike@flooze.com','','','','','Mind','','','0000-00-00 00:00:00','0000-00-00 00:00:00','127.0.0.2'),(4,'test1234','lakslsaas',5,'','flloozz','mike@geeklabs.com','','','','','Mind','','','0000-00-00 00:00:00','0000-00-00 00:00:00','127.0.0.2');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glfielddesc`
--

DROP TABLE IF EXISTS `glfielddesc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glfielddesc` (
  `uniq` int(10) NOT NULL AUTO_INCREMENT,
  `table` varchar(20) DEFAULT NULL,
  `field` varchar(80) DEFAULT NULL,
  `display` varchar(80) DEFAULT NULL,
  `description` text,
  `specialformat` varchar(80) DEFAULT '',
  `placeholder` varchar(80) DEFAULT '',
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  KEY `table` (`table`) USING BTREE,
  KEY `field` (`field`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glfielddesc`
--

LOCK TABLES `glfielddesc` WRITE;
/*!40000 ALTER TABLE `glfielddesc` DISABLE KEYS */;
INSERT INTO `glfielddesc` VALUES (1,'*','name','Name','The person or entities full name','',''),(3,'*','login','Login','a person or entity that uses the system as an admin has a login. a customer has an account number','','');
/*!40000 ALTER TABLE `glfielddesc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gltabledesc`
--

DROP TABLE IF EXISTS `gltabledesc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gltabledesc` (
  `uniq` int(10) NOT NULL AUTO_INCREMENT,
  `table` varchar(20) DEFAULT NULL,
  `display` varchar(80) DEFAULT NULL,
  `description` text,
  `specialformat` varchar(80) DEFAULT '',
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  KEY `table` (`table`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gltabledesc`
--

LOCK TABLES `gltabledesc` WRITE;
/*!40000 ALTER TABLE `gltabledesc` DISABLE KEYS */;
INSERT INTO `gltabledesc` VALUES (1,'users','Users','Admin and special access users of this system. This table and userperms works together to define a user and what they can access','mode=manageuser&uniq=$uniq');
/*!40000 ALTER TABLE `gltabledesc` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Table structure for table `perms`
--

DROP TABLE IF EXISTS `perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perms` (
  `uniq` int(10) NOT NULL AUTO_INCREMENT,
  `cat` varchar(40) NOT NULL DEFAULT 'Misc',
  `subcat` varchar(20) DEFAULT '',
  `perm` varchar(10) NOT NULL DEFAULT '',
  `role` varchar(10) NOT NULL DEFAULT '',
  `seclevel` int(10) DEFAULT '5',
  `description` varchar(80) DEFAULT '',
  `longdesc` text NOT NULL,
  `active` int(1) DEFAULT '1',
  `menu` int(1) DEFAULT '1',
  `icon` varchar(40) DEFAULT '',
  `url` varchar(80) DEFAULT '',
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  KEY `cat` (`perm`) USING BTREE,
  KEY `perm` (`perm`) USING BTREE,
  KEY `role` (`role`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perms`
--

LOCK TABLES `perms` WRITE;
/*!40000 ALTER TABLE `perms` DISABLE KEYS */;
INSERT INTO `perms` VALUES (1,'Top Menu','','reports','',5,'Reports','Top Menu access to reporting system',1,0,'',''),(2,'User Management','','sysusers','',5,'Edit System Users','Permissions and Users',1,0,'',''),(3,'Top Menu','','data','',5,'Edit Data, Tables','Add, Edit, Delete Data',1,1,'','');
/*!40000 ALTER TABLE `perms` ENABLE KEYS */;
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
INSERT INTO `reports` VALUES (10,'lookup','FirstReport','People Report',NULL,90,'','','select * from people where login = &apos;input1&apos; and created >= &#39;fromdate&#39; and created < &#39;todate&#39; ',NULL,NULL,'integer,double|decimal|bit,level',NULL,'Login','',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'','REPORT','HTML|CSV|XLS|PDF','NOW|OFFLINE|EMAIL',NULL,NULL,NULL,NULL,NULL,'D',40,0.0000,0,'2014-11-17','2014-11-17 16:12:14'),(30,'lookup','allpeople','allpeople report',NULL,90,'','','select * from people where login = &apos;input1&apos; and created >= &#39;fromdate&#39; and created < &#39;todate&#39; ',NULL,NULL,'integer,double|decimal|bit,level',NULL,'Login','',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL,'','REPORT','HTML|CSV|XLS|PDF','NOW|OFFLINE|EMAIL',NULL,NULL,NULL,NULL,NULL,'D',40,0.0058,69,'2014-11-17','2014-11-18 14:38:03');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `state`
--

DROP TABLE IF EXISTS `state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `state` (
  `uniq` int(10) NOT NULL AUTO_INCREMENT,
  `state` varchar(20) DEFAULT NULL,
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  UNIQUE KEY `state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `state`
--

LOCK TABLES `state` WRITE;
/*!40000 ALTER TABLE `state` DISABLE KEYS */;
INSERT INTO `state` VALUES (23,'Confusion','2012-02-17 20:47:19'),(24,'Emergency','2013-03-04 22:07:31'),(25,'Art','2013-03-04 22:08:39'),(26,'Mind','2013-03-04 22:08:57'),(27,'California','2013-03-04 22:09:32'),(28,'Tennessee','2013-03-04 22:09:40'),(29,'New York','2013-03-04 22:09:50');
/*!40000 ALTER TABLE `state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userperms`
--

DROP TABLE IF EXISTS `userperms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userperms` (
  `uniq` int(10) NOT NULL AUTO_INCREMENT,
  `portal` varchar(10) NOT NULL DEFAULT '',
  `login` varchar(20) NOT NULL DEFAULT '',
  `perm` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  KEY `portal` (`portal`) USING BTREE,
  KEY `login` (`login`) USING BTREE,
  KEY `perm` (`perm`) USING BTREE,
  CONSTRAINT `userperms_ibfk_1` FOREIGN KEY (`perm`) REFERENCES `perms` (`perm`),
  CONSTRAINT `userperms_ibfk_2` FOREIGN KEY (`login`) REFERENCES `users` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userperms`
--

LOCK TABLES `userperms` WRITE;
/*!40000 ALTER TABLE `userperms` DISABLE KEYS */;
INSERT INTO `userperms` VALUES (2,'*','mike','data'),(3,'*','mike','reports'),(5,'*','mike','sysusers'),(25,'*','admin','sysusers'),(51,'','admin','data'),(52,'','admin','reports'),(53,'','admin','sysusers');
/*!40000 ALTER TABLE `userperms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `uniq` bigint(20) NOT NULL AUTO_INCREMENT,
  `login` varchar(20) NOT NULL DEFAULT 'invalid',
  `passwd` varchar(80) NOT NULL DEFAULT '87654321ABCDEF',
  `level` int(5) NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email` varchar(200) DEFAULT '',
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  UNIQUE KEY `login` (`login`),
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'mike','1234',99,'','0000-00-00 00:00:00','2014-12-05 12:12:32',''),(2,'admin','1234',90,'Glass Hole','0000-00-00 00:00:00','2014-12-07 14:44:38','mike@geeklabs.com');
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

-- Dump completed on 2014-12-07 14:14:06
