#This is my sample table(s) for GLASS. What I'm playing with. 

DROP TABLE IF EXISTS `people`;
CREATE TABLE `people` (
  `uniq` bigint(20) NOT NULL AUTO_INCREMENT,
  `login` varchar(20) NOT NULL DEFAULT 'invalid',
  `passwd` varchar(80) NOT NULL DEFAULT '87654321QbDyp33f',
  `level` int(5) not null default 0 , 
  `name` varchar(20) NOT NULL DEFAULT '',
  `content` text,
  `integer` int(10) not null default 0 , 
  `double` double(16,2) not null default 0 ,
  `decimal` decimal(16,2) not null default 0 ,
  `bit` bit(8) not null default 0 ,
  `point` POINT,
  `polygon` POLYGON , 
  `dateexample` date NOT NULL DEFAULT '0000-00-00',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uniq`),
  UNIQUE KEY `id` (`uniq`),
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


insert into people (login,passwd,name) values ('Mike','#1234@1234$1234','Mike Harrison') ; 

