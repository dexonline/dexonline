CREATE TABLE `student` (
  `userId` int NOT NULL DEFAULT '0',
  `nick` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `institution` varchar(32) COLLATE utf8mb4_romanian_ci DEFAULT 'ugal',
  `department` varchar(10) COLLATE utf8mb4_romanian_ci DEFAULT '',
  `year` year DEFAULT NULL,
  `allocated_hours` tinyint DEFAULT NULL,
  `allocated_pages` tinyint DEFAULT NULL,
  `actual_no_pages` tinyint DEFAULT NULL,
  `allocated_source_id` int DEFAULT NULL,
  `letter` char(1) COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `page_start` smallint DEFAULT NULL,
  `page_end` smallint DEFAULT NULL,
  `first_word` varchar(32) COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `last_word` varchar(32) COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `info` varchar(100) COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;

insert into Variable (name, value) values ('OCRInfo.statsTS', NOW());
