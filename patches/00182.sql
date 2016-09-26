CREATE TABLE IF NOT EXISTS `Abbreviation` ( 
	`id` Int( 11 ) AUTO_INCREMENT NOT NULL,
	`sourceId` Int( 11 ) NOT NULL,
	`short` varchar(255) NULL,
	`long` varchar(255) NULL,
	`ambiguous` tinyint( 1 ) NULL DEFAULT '0',
	`caseSensitive` tinyint(1) NULL,
	`createDate` Int( 11 ) NOT NULL,
	`modDate` Int( 11 ) NOT NULL,
	`modUserId` Int( 11 ) NOT NULL,
	CONSTRAINT `Id` UNIQUE( `id` ) )
COLLATE = utf8_romanian_ci
ENGINE = MyISAM
AUTO_INCREMENT = 1; 


CREATE INDEX `CreateDate` USING BTREE ON `Abbreviation`( `createDate` );
CREATE INDEX `short` USING BTREE ON `Abbreviation`( `short` );
CREATE INDEX `ModDate` USING BTREE ON `Abbreviation`( `modDate` );
CREATE INDEX `sourceId` USING BTREE ON `Abbreviation`( `sourceId` );
