CREATE TABLE `OCR_stats` (
  `userId` int(11) NOT NULL DEFAULT '0',
  `editorId` int(11) DEFAULT NULL,
  `sourceId` int(11) NOT NULL DEFAULT '0',
  `status` enum('raw','published') COLLATE utf8mb4_romanian_ci DEFAULT 'raw',
  `defCnt` int(11) NOT NULL DEFAULT 0,
  `defTotalSize` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;


insert into OCR_stats SELECT userId, editorId, sourceId, status, count(*) defCnt, sum(char_length(ocrText)) defTotalSize FROM OCR GROUP BY userId, editorId, sourceId, status;
