CREATE TABLE `SourceArticles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shortName` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `publisher` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `year` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT '',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `normative` tinyint(1) NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);
