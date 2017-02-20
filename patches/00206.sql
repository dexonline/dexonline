CREATE TABLE SourceType (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `description` varchar(255) NOT NULL,
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

INSERT INTO SourceType (id, name, description) VALUES
(1, 'Explicativ', 'Dicționare care au ca scop principal definirea cuvintelor'),
(2, 'Morfologic', 'Dicționare care descriu formele corecte ale cuvintelor'),
(3, 'Relațional', 'Dicționare care descriu relațiile dintre cuvinte'),
(4, 'Etimologic', 'Dicționare care prezintă etimologia și istoria cuvintelor'),
(5, 'Specializat', 'Dicționare de nișă, de jargon etc.'),
(6, 'Enciclopedic', 'Dicționare care au componentă enciclopedică sau prezintă explicații a unor nume proprii'),
(7, 'Argou', 'Dicționare de argou');

CREATE TABLE OrthographicReforms (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `year` smallint DEFAULT NULL,
  `majorReformId` int(11) DEFAULT NULL COMMENT '# - minor, NULL - major',
  `description` varchar(255) DEFAULT NULL,
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

INSERT INTO OrthographicReforms(`id`, `name`, `year`, `majorReformId`) VALUES
(-1, 'dexonline', NULL, NULL),
(1, 'DOOM2', 2005, 3),
(2, 'Îndreptar 5', 2001, 3),
(3, 'Reforma ortografică 1993 (â și sunt)', 1993, NULL),
(4, 'Îndreptar 4', 1983, 11),
(5, 'DOOM', 1982, 11),
(6, 'Îndreptar 3', 1971, 11),
(7, 'Îndreptar 2', 1965, 11),
(8, 'Îndreptar', 1960, 11),
(9, 'Îndreptar de punctuație', 1956, 11),
(10, 'Dicționar ortoepic', 1956, 11),
(11, 'Mic dicționar ortografic', 1953, NULL),
(12, 'Reforma ortografică 1932', 1932, NULL),
(13, 'Reforma ortografică 1904', 1904, NULL);

ALTER TABLE Source
ADD COLUMN sourceTypeId int(11) DEFAULT NULL AFTER year,
ADD COLUMN managerId int(11) DEFAULT NULL COMMENT 'userId' AFTER sourceTypeId,
ADD COLUMN importType tinyint NOT NULL DEFAULT 0 COMMENT '0 - mix, 1 - OCR, 2 - manual, 3 - auto (script)' AFTER managerId,
ADD COLUMN reformId int(11) DEFAULT NULL AFTER importType;

UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 2, reformId = 3 WHERE id = 1; # DEX 98
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 2, reformId = 3 WHERE id = 2; # DEX 96
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 4 WHERE id = 3; # DEX 84
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 7 WHERE id = 4; # DEX 75
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 7 WHERE id = 5; # DEX-S
UPDATE Source SET sourceTypeId = 3, managerId = 1, importType = 3, reformId = 3 WHERE id = 6; # Sinonime
UPDATE Source SET sourceTypeId = 3, managerId = 1, importType = 3, reformId = 3 WHERE id = 7; # Antonime
UPDATE Source SET sourceTypeId = 2, managerId = 1, importType = 3, reformId = 3 WHERE id = 8; # Ortografic
UPDATE Source SET sourceTypeId = 1, managerId = 1, importType = 3, reformId = 3 WHERE id = 9; # NODEX
UPDATE Source SET sourceTypeId = 5, managerId = NULL, importType = 0, reformId = 7 WHERE id = 10; # DAR
UPDATE Source SET sourceTypeId = 5, managerId = NULL, importType = 0, reformId = 7 WHERE id = 11; # DGE
UPDATE Source SET sourceTypeId = 4, managerId = NULL, importType = 0, reformId = 7 WHERE id = 12; # DER
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 7 WHERE id = 13; # DLRA
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 7 WHERE id = 14; # DLRC
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 11 WHERE id = 15; # DLRM
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 7 WHERE id = 16; # DMLR
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 7 WHERE id = 17; # DN
UPDATE Source SET sourceTypeId = 2, managerId = NULL, importType = 0, reformId = 7 WHERE id = 18; # DOOM
UPDATE Source SET sourceTypeId = 2, managerId = 471, importType = 0, reformId = 1 WHERE id = 19; # DOOM 2
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 1 WHERE id = 20; # MDA
UPDATE Source SET sourceTypeId = 1, managerId = N471, importType = 3, reformId = 3 WHERE id = 21; # MDN 00
UPDATE Source SET sourceTypeId = 5, managerId = NULL, importType = 0, reformId = 1 WHERE id = 22; # Neoficial
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 7 WHERE id = 23; # DLRLV
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 7 WHERE id = 24; # DLRLC
UPDATE Source SET sourceTypeId = 6, managerId = NULL, importType = 0, reformId = 3 WHERE id = 25; # DE
UPDATE Source SET sourceTypeId = 7, managerId = NULL, importType = 0, reformId = 3 WHERE id = 26; # Argou
UPDATE Source SET sourceTypeId = 5, managerId = 471, importType = 3, reformId = 3 WHERE id = 28; # Petro-Sedim
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 1 WHERE id = 27; # DEX 09
UPDATE Source SET sourceTypeId = 5, managerId = NULL, importType = 0, reformId = 1 WHERE id = 29; # DSL
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 1 WHERE id = 30; # DCR2
UPDATE Source SET sourceTypeId = 5, managerId = 471, importType = 3, reformId = 1 WHERE id = 31; # GTA
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 3 WHERE id = 32; # DTM
UPDATE Source SET sourceTypeId = 6, managerId = NULL, importType = 0, reformId = 3 WHERE id = 33; # D.Religios
UPDATE Source SET sourceTypeId = 1, managerId = 471, importType = 0, reformId = 0 WHERE id = 34; # dexonline
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = NULL WHERE id = 35; # Scriban
UPDATE Source SET sourceTypeId = 6, managerId = NULL, importType = 0, reformId = 3 WHERE id = 36; # Mitologic
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 1 WHERE id = 37; # DEI
UPDATE Source SET sourceTypeId = 2, managerId = 471, importType = 0, reformId = 1 WHERE id = 38; # DOR
UPDATE Source SET sourceTypeId = 5, managerId = NULL, importType = 0, reformId = 7 WHERE id = 39; # DGL
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 1 WHERE id = 40; # DEX 12
UPDATE Source SET sourceTypeId = 5, managerId = 471, importType = 3, reformId = 1 WHERE id = 41; # DRAM
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 12 WHERE id = 42; # Șăineanu, ed. VI
UPDATE Source SET sourceTypeId = 5, managerId = NULL, importType = 0, reformId = 7 WHERE id = 43; # DTL
UPDATE Source SET sourceTypeId = 5, managerId = NULL, importType = 0, reformId = NULL WHERE id = 44; # DAS
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = NULL WHERE id = 45; # Șăineanu, ed. I
UPDATE Source SET sourceTypeId = 6, managerId = NULL, importType = 0, reformId = NULL WHERE id = 46; # DFLR
UPDATE Source SET sourceTypeId = 3, managerId = NULL, importType = 0, reformId = NULL WHERE id = 47; # DS
UPDATE Source SET sourceTypeId = 3, managerId = NULL, importType = 0, reformId = NULL WHERE id = 48; # DA
UPDATE Source SET sourceTypeId = 3, managerId = NULL, importType = 0, reformId = NULL WHERE id = 49; # DS5
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = NULL WHERE id = 50; # DELR
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = NULL WHERE id = 51; # DELLR
UPDATE Source SET sourceTypeId = 5, managerId = NULL, importType = 0, reformId = NULL WHERE id = 52; # DMG
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = NULL WHERE id = 53; # MDA2
UPDATE Source SET sourceTypeId = 4, managerId = NULL, importType = 0, reformId = NULL WHERE id = 54; # DELRIE
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = NULL WHERE id = 55; # MDN 08
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 1 WHERE id = 56; # DRAM 2015
UPDATE Source SET sourceTypeId = 6, managerId = NULL, importType = 0, reformId = 7 WHERE id = 57; # Onomastic
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = NULL WHERE id = 58; # CADE
UPDATE Source SET sourceTypeId = 3, managerId = NULL, importType = 0, reformId = 7 WHERE id = 59; # Sinonime82
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = NULL WHERE id = 60; # DifSem
UPDATE Source SET sourceTypeId = 4, managerId = NULL, importType = 0, reformId = 7 WHERE id = 61; # GER
UPDATE Source SET sourceTypeId = 4, managerId = NULL, importType = 0, reformId = 7 WHERE id = 62; # GAER
UPDATE Source SET sourceTypeId = 6, managerId = NULL, importType = 0, reformId = 7 WHERE id = 63; # DFL
UPDATE Source SET sourceTypeId = 6, managerId = NULL, importType = 0, reformId = 7 WHERE id = 64; # Etnobotanic
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 1 WHERE id = 65; # DEX 16
UPDATE Source SET sourceTypeId = 5, managerId = NULL, importType = 0, reformId = 7 WHERE id = 66; # DETS
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 1 WHERE id = 67; # DEXI
UPDATE Source SET sourceTypeId = 3, managerId = NULL, importType = 0, reformId = 1 WHERE id = 68; # DGS
UPDATE Source SET sourceTypeId = 1, managerId = NULL, importType = 0, reformId = 1 WHERE id = 69; # DEXLRA
UPDATE Source SET sourceTypeId = 2, managerId = NULL, importType = 0, reformId = 3 WHERE id = 70; # Ortografic 01
UPDATE Source SET sourceTypeId = 5, managerId = NULL, importType = 0, reformId = 7 WHERE id = 71; # Epitete