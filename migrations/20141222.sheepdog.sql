DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `uniq` int(10) NOT NULL AUTO_INCREMENT,
  `migration` date NOT NULL,
  `filename` varchar(40) NOT NULL DEFAULT '',
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uniq`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=latin1;
insert into migrations (migration,filename) values (now(),'20141222.sheepdog.sql') ;