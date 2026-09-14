CREATE TABLE `Proverb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `displayDate` date DEFAULT NULL,
  `definitionId` int(11) DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `idArtist` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int(11) NOT NULL DEFAULT '0',
  `modDate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `displayDate` (`displayDate`),
  KEY `createDate` (`createDate`),
  KEY `modDate` (`modDate`)
) ENGINE=InnoDB;
