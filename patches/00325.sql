CREATE TABLE `VideoClip` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `videoId` varchar(12) COLLATE utf8mb4_romanian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;

CREATE TABLE `Subtitle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clipId` smallint(5) unsigned DEFAULT NULL,
  `start` smallint(5) unsigned DEFAULT NULL,
  `word` varchar(64) COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clipId` (`clipId`),
  KEY `word` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci ROW_FORMAT=COMPRESSED;
