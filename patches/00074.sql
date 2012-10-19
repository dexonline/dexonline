-- For some reason the table already exists on my machine. Was it created directly in production? :-)
CREATE TABLE if not exists `WordOfTheMonth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `displayDate` date DEFAULT NULL,
  `definitionId` int(11) NOT NULL,
  `article` varchar(255) DEFAULT '',
  `image` varchar(255) DEFAULT NULL,
  `description` char(255) DEFAULT NULL,
  `creationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `displayDate` (`displayDate`)
) AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

truncate table WordOfTheMonth;

INSERT INTO `WordOfTheMonth` VALUES (1,'2012-04-01',61866,'Punctul','2012/salutar.jpg','emisiunea lui Gâdea','2012-07-01 15:06:02'),(2,'2012-07-01',32643,'II._ă_sau_e%3F','2012/plagiat.jpg','plagiatul lui Ponta','2012-07-01 15:07:28'),(3,'2012-05-01',9354,'I._e_sau_ea%3F','2012/empatie.jpg','cel mai căutat cuvînt al lunii','2012-10-02 00:59:27'),(4,'2012-06-01',51841,'Niciun_sau_nici_un%3F','','cel mai căutat cuvînt al lunii','2012-10-02 01:07:25'),(5,'2012-08-01',4778,'Mi-ar_*place','2012/boicot.jpg','cel mai căutat cuvînt al lunii + boicotul la referendum','2012-10-02 01:10:07'),(6,'2012-09-01',32183,'Semnul_întrebării','2012/cvorum.jpg','cel mai căutat cuvînt al lunii + discuțiile despre cvorumul la referendum','2012-10-02 01:11:37'),(7,'2012-10-01',9354,'Calcuri_inutile','2012/empatie.jpg','cel mai căutat cuvînt al lunii','2012-10-04 01:24:43');
