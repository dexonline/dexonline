CREATE TABLE `OCR` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sourceId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `ocrText` text COLLATE utf8_romanian_ci,
  `status` enum('raw','published') COLLATE utf8_romanian_ci DEFAULT 'raw',
  `editorId` int(11) DEFAULT NULL,
  `definitionId` int(11) DEFAULT NULL,
  `dateModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dateAdded` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `definitionId` (`definitionId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;
