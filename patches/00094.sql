CREATE TABLE `OCRLot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sourceId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `fileName` varchar(100) COLLATE utf8_romanian_ci NOT NULL,
  `fileSize` int(11) NOT NULL,
  `status` enum('started','done') COLLATE utf8_romanian_ci DEFAULT 'started',
  `lastModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `startedAt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `line` (`sourceId`,`fileName`(50))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

alter table OCR add column lotId int(11) DEFAULT NULL after id;
alter table OCR add unique index line(sourceId,ocrText(50));
