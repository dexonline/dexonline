CREATE TABLE `WikiSection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageId` int(11) NOT NULL,
  `section` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pageId` (`pageId`),
  KEY `section` (`section`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
