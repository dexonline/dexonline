--
CREATE TABLE `WordOfTheDay` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `userId` int(11) NOT NULL,
 `displayDate` date DEFAULT NULL,
 `priority` int(11) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
CREATE TABLE `WordOfTheDayRel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wotdId` int(11) NOT NULL,
  `refId` int(11) NOT NULL,
  `refType` ENUM('Definition') DEFAULT 'Definition',
  PRIMARY KEY (`id`),
  KEY (`wotdId`, `refId`, `refType`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

