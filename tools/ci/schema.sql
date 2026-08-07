
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `Abbreviation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Abbreviation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sourceId` int NOT NULL DEFAULT '0',
  `short` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `internalRep` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `ambiguous` tinyint(1) NOT NULL DEFAULT '0',
  `caseSensitive` tinyint(1) NOT NULL DEFAULT '0',
  `enforced` tinyint(1) NOT NULL DEFAULT '0',
  `html` tinyint(1) NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  `modUserId` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `createDate` (`createDate`),
  KEY `short` (`short`),
  KEY `modDate` (`modDate`),
  KEY `sourceId` (`sourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `AccuracyProject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AccuracyProject` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `ownerId` int NOT NULL DEFAULT '0',
  `userId` int NOT NULL DEFAULT '0',
  `sourceId` int NOT NULL DEFAULT '0',
  `lexiconPrefix` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `startDate` date NOT NULL DEFAULT '0000-00-00',
  `endDate` date NOT NULL DEFAULT '0000-00-00',
  `visibility` int NOT NULL DEFAULT '0',
  `defCount` int NOT NULL DEFAULT '0',
  `errorRate` double NOT NULL DEFAULT '0',
  `speed` double NOT NULL DEFAULT '0',
  `totalLength` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `AccuracyRecord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AccuracyRecord` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projectId` int NOT NULL DEFAULT '0',
  `definitionId` int NOT NULL DEFAULT '0',
  `reviewed` int NOT NULL DEFAULT '0',
  `errors` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `projectId_2` (`projectId`,`definitionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `AdsClick`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AdsClick` (
  `id` int NOT NULL AUTO_INCREMENT,
  `skey` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `ip` int unsigned NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `AdsLink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AdsLink` (
  `skey` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ConstraintMap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ConstraintMap` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `inflectionId` int NOT NULL DEFAULT '0',
  `variant` int NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Cookie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Cookie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cookieString` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `userId` int DEFAULT NULL,
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cookieString` (`cookieString`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `CrawlerIgnoredUrl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `CrawlerIgnoredUrl` (
  `id` int NOT NULL AUTO_INCREMENT,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `failureCount` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `url` (`url`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `CrawlerPhrase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `CrawlerPhrase` (
  `id` int NOT NULL AUTO_INCREMENT,
  `crawlerUrlId` int NOT NULL DEFAULT '0',
  `contents` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL,
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `crawlerUrlId` (`crawlerUrlId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `CrawlerUrl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `CrawlerUrl` (
  `id` int NOT NULL AUTO_INCREMENT,
  `siteId` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `author` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `url` (`url`(191)),
  KEY `siteId` (`siteId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Definition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Definition` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL DEFAULT '0',
  `sourceId` tinyint unsigned NOT NULL DEFAULT '0',
  `lexicon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `internalRep` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci,
  `status` tinyint unsigned NOT NULL DEFAULT '0',
  `hasAmbiguousAbbreviations` tinyint unsigned NOT NULL DEFAULT '0',
  `rareGlyphs` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `structured` tinyint(1) NOT NULL DEFAULT '0',
  `volume` tinyint unsigned NOT NULL DEFAULT '0',
  `page` smallint unsigned NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  `modUserId` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `UserId` (`userId`),
  KEY `CreateDate` (`createDate`),
  KEY `ModDate` (`modDate`),
  KEY `Status` (`status`),
  KEY `Lexicon` (`lexicon`),
  KEY `sourceId` (`sourceId`),
  KEY `abbrevReview` (`hasAmbiguousAbbreviations`),
  KEY `structured` (`structured`),
  KEY `volume` (`volume`,`page`),
  KEY `rareGlyphs` (`rareGlyphs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `DefinitionSimple`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `DefinitionSimple` (
  `id` int NOT NULL AUTO_INCREMENT,
  `definition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL,
  `lexicon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  `millShown` bigint NOT NULL DEFAULT '0',
  `millGuessed` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lexicon` (`lexicon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `DefinitionVersion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `DefinitionVersion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `definitionId` int NOT NULL DEFAULT '0',
  `action` tinyint unsigned NOT NULL DEFAULT '0',
  `sourceId` tinyint unsigned NOT NULL DEFAULT '0',
  `lexicon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `internalRep` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci,
  `status` tinyint unsigned NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modUserId` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `definitionId` (`definitionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Donation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Donation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `amount` int NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `userId` int NOT NULL DEFAULT '0',
  `source` int NOT NULL DEFAULT '0',
  `emailSent` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Entry` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `structStatus` int NOT NULL DEFAULT '1',
  `structuristId` int NOT NULL DEFAULT '0',
  `adult` int NOT NULL DEFAULT '0',
  `multipleMains` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  `modUserId` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `description` (`description`),
  KEY `structuristId` (`structuristId`),
  KEY `structStatus` (`structStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `EntryDefinition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `EntryDefinition` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entryId` int NOT NULL DEFAULT '0',
  `definitionId` int NOT NULL DEFAULT '0',
  `entryRank` int NOT NULL DEFAULT '0',
  `definitionRank` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `entryId_2` (`entryId`,`definitionRank`),
  KEY `definitionId_2` (`definitionId`,`entryRank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `EntryLexeme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `EntryLexeme` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entryId` int NOT NULL DEFAULT '0',
  `lexemeId` int NOT NULL DEFAULT '0',
  `entryRank` int NOT NULL DEFAULT '0',
  `lexemeRank` int NOT NULL DEFAULT '0',
  `main` int NOT NULL DEFAULT '1',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `entryId_3` (`entryId`,`main`),
  KEY `entryId_2` (`entryId`,`lexemeRank`),
  KEY `lexemeId` (`lexemeId`,`entryRank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ExpertDefinitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ExpertDefinitions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `keyword` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `title` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `content` varchar(4096) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ExpressionOfTheMonth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ExpressionOfTheMonth` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `displayDate` date DEFAULT NULL,
  `definitionId` int NOT NULL DEFAULT '0',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `idArtist` int NOT NULL DEFAULT '0',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `displayDate` (`displayDate`),
  KEY `createDate` (`createDate`),
  KEY `modDate` (`modDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ExpressionOfTheMonthOld`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ExpressionOfTheMonthOld` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `displayDate` date DEFAULT NULL,
  `definitionId` int NOT NULL DEFAULT '0',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `idArtist` int NOT NULL DEFAULT '0',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `displayDate` (`displayDate`),
  KEY `createDate` (`createDate`),
  KEY `modDate` (`modDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Fragment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Fragment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lexemeId` int NOT NULL DEFAULT '0',
  `partId` int NOT NULL DEFAULT '0',
  `capitalized` int NOT NULL DEFAULT '0',
  `accented` int NOT NULL DEFAULT '1',
  `declension` int NOT NULL DEFAULT '0',
  `rank` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lexemId` (`lexemeId`),
  KEY `partId` (`partId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `FullTextIndex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `FullTextIndex` (
  `lexemeId` int NOT NULL DEFAULT '0',
  `definitionId` int NOT NULL DEFAULT '0',
  `position` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`lexemeId`,`definitionId`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `HarmonizeModel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `HarmonizeModel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `modelType` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `modelNumber` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `tagId` int NOT NULL DEFAULT '0',
  `newModelType` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `newModelNumber` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tagId` (`tagId`),
  KEY `modelType_2` (`modelType`,`modelNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `HarmonizeTag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `HarmonizeTag` (
  `id` int NOT NULL AUTO_INCREMENT,
  `modelType` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `modelNumber` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `tagId` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tagId` (`tagId`),
  KEY `modelType_2` (`modelType`,`modelNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `InflectedForm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `InflectedForm` (
  `id` int NOT NULL AUTO_INCREMENT,
  `form` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `formNoAccent` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `formUtf8General` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `lexemeId` int NOT NULL DEFAULT '0',
  `inflectionId` int NOT NULL DEFAULT '0',
  `variant` int NOT NULL DEFAULT '0',
  `recommended` int DEFAULT NULL,
  `apheresis` tinyint NOT NULL DEFAULT '0',
  `apocope` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `wl_form` (`form`),
  KEY `wl_neaccentuat` (`formNoAccent`),
  KEY `wl_utf8_general` (`formUtf8General`),
  KEY `wl_analyse` (`inflectionId`),
  KEY `lexemId` (`lexemeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Inflection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Inflection` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `modelType` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `rank` int DEFAULT NULL,
  `gender` int NOT NULL DEFAULT '0',
  `number` int NOT NULL DEFAULT '0',
  `case` int NOT NULL DEFAULT '0',
  `article` int NOT NULL DEFAULT '0',
  `animate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Lexeme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Lexeme` (
  `id` int NOT NULL AUTO_INCREMENT,
  `form` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `formNoAccent` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `formUtf8General` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `reverse` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `number` int DEFAULT NULL,
  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `noAccent` int NOT NULL DEFAULT '0',
  `consistentAccent` int NOT NULL DEFAULT '0',
  `frequency` float DEFAULT '0',
  `hyphenations` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `pronunciations` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `stopWord` int NOT NULL DEFAULT '0',
  `compound` int NOT NULL DEFAULT '0',
  `modelType` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `modelNumber` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `restriction` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `staleParadigm` tinyint NOT NULL DEFAULT '0',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `hasApheresis` tinyint NOT NULL DEFAULT '0',
  `hasApocope` tinyint NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lexem_forma` (`form`),
  KEY `lexem_utf8_general` (`formUtf8General`),
  KEY `lexem_invers` (`reverse`),
  KEY `ModDate` (`modDate`),
  KEY `lexem_neaccentuat_2` (`formNoAccent`,`description`),
  KEY `modelType` (`modelType`),
  KEY `staleParadigm` (`staleParadigm`),
  KEY `pronunciations` (`pronunciations`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `LexemeSource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `LexemeSource` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lexemeId` int NOT NULL DEFAULT '0',
  `sourceId` int NOT NULL DEFAULT '0',
  `lexemeRank` int NOT NULL DEFAULT '0',
  `sourceRank` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lexemeId` (`lexemeId`,`sourceRank`),
  KEY `sourceId` (`sourceId`,`lexemeRank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Loc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Loc` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `form` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `version` (`version`,`form`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Meaning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Meaning` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parentId` int NOT NULL DEFAULT '0',
  `type` int NOT NULL DEFAULT '0',
  `displayOrder` int NOT NULL DEFAULT '0',
  `breadcrumb` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `userId` int NOT NULL DEFAULT '0',
  `treeId` int NOT NULL DEFAULT '0',
  `internalRep` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci,
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `treeId` (`treeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `MeaningSource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `MeaningSource` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meaningId` int NOT NULL DEFAULT '0',
  `sourceId` int NOT NULL DEFAULT '0',
  `meaningRank` int NOT NULL DEFAULT '0',
  `sourceRank` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `meaningId_2` (`meaningId`,`sourceRank`),
  KEY `sourceId` (`sourceId`,`meaningRank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Mention`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Mention` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meaningId` int NOT NULL DEFAULT '0',
  `objectId` int NOT NULL DEFAULT '0',
  `objectType` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `meaningId` (`meaningId`),
  KEY `objectId` (`objectId`,`objectType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `MillData`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `MillData` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meaningId` int NOT NULL DEFAULT '0',
  `word` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `posMask` tinyint NOT NULL DEFAULT '0',
  `internalRep` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL,
  `shown` int NOT NULL DEFAULT '0',
  `guessed` int NOT NULL DEFAULT '0',
  `ratio` float NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `meaningId` (`meaningId`),
  KEY `word` (`word`),
  KEY `ratio` (`ratio`),
  KEY `modDate` (`modDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Model` (
  `id` int NOT NULL AUTO_INCREMENT,
  `modelType` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `number` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL,
  `exponent` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `morf` (`modelType`,`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ModelDescription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ModelDescription` (
  `id` int NOT NULL AUTO_INCREMENT,
  `modelId` int NOT NULL DEFAULT '0',
  `inflectionId` int NOT NULL DEFAULT '0',
  `variant` int NOT NULL DEFAULT '0',
  `applOrder` int NOT NULL DEFAULT '0',
  `recommended` tinyint(1) NOT NULL DEFAULT '0',
  `hasApocope` tinyint NOT NULL DEFAULT '0',
  `transformId` int NOT NULL DEFAULT '0',
  `accentShift` int NOT NULL DEFAULT '0',
  `vowel` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `md_transf` (`transformId`),
  KEY `modelId` (`modelId`,`inflectionId`,`variant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ModelType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ModelType` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `canonical` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mt_value` (`code`),
  KEY `mt_canonical` (`canonical`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `OCR`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `OCR` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lotId` int DEFAULT NULL,
  `sourceId` int NOT NULL DEFAULT '0',
  `userId` int NOT NULL DEFAULT '0',
  `ocrText` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci,
  `status` enum('raw','published') CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT 'raw',
  `editorId` int DEFAULT NULL,
  `definitionId` int DEFAULT NULL,
  `dateModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dateAdded` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `line` (`sourceId`,`ocrText`(50)),
  KEY `definitionId` (`definitionId`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `OCRLot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `OCRLot` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sourceId` int NOT NULL DEFAULT '0',
  `userId` int NOT NULL DEFAULT '0',
  `fileName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `fileSize` int NOT NULL DEFAULT '0',
  `status` enum('started','done') CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT 'started',
  `lastModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `startedAt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `line` (`sourceId`,`fileName`(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ObjectTag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ObjectTag` (
  `id` int NOT NULL AUTO_INCREMENT,
  `objectId` int NOT NULL DEFAULT '0',
  `objectType` int NOT NULL DEFAULT '0',
  `tagId` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `objectId` (`objectId`,`objectType`),
  KEY `tagId` (`tagId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `OrthographicReforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `OrthographicReforms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `year` smallint DEFAULT NULL,
  `majorReformId` int DEFAULT NULL COMMENT '# - minor, NULL - major',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `PageIndex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PageIndex` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sourceId` int NOT NULL DEFAULT '0',
  `volume` int NOT NULL DEFAULT '0',
  `page` int NOT NULL DEFAULT '0',
  `word` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `number` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  `modUserId` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sourceId` (`sourceId`,`volume`,`page`),
  KEY `sourceId_2` (`sourceId`,`word`),
  KEY `CreateDate` (`createDate`),
  KEY `ModDate` (`modDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ParticipleModel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ParticipleModel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `verbModel` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `adjectiveModel` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pm_verb_model` (`verbModel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `PasswordToken`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PasswordToken` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL DEFAULT '0',
  `token` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `RandomWord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `RandomWord` (
  `id` int NOT NULL DEFAULT '0',
  `cuv` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `surse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `seq` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `seq` (`seq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `RecentLink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `RecentLink` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL DEFAULT '0',
  `visitDate` int NOT NULL DEFAULT '0',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `UserId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Relation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meaningId` int NOT NULL DEFAULT '0',
  `treeId` int NOT NULL DEFAULT '0',
  `type` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `meaningId` (`meaningId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `SimilarSource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `SimilarSource` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sourceId` int NOT NULL DEFAULT '0',
  `similarSource` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sourceId` (`sourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Source` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shortName` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `urlName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `publisher` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `year` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `sourceTypeId` int DEFAULT NULL,
  `managerId` int DEFAULT NULL COMMENT 'userId',
  `importType` tinyint NOT NULL DEFAULT '0' COMMENT '0 - mix, 1 - OCR, 2 - manual, 3 - auto (script)',
  `reformId` int DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT '',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `courtesyLink` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `courtesyText` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `canModerate` tinyint(1) DEFAULT NULL,
  `normative` tinyint(1) NOT NULL DEFAULT '0',
  `displayOrder` smallint DEFAULT NULL,
  `canDistribute` int NOT NULL DEFAULT '0',
  `defCount` int NOT NULL DEFAULT '-1',
  `ourDefCount` int NOT NULL DEFAULT '0',
  `percentComplete` decimal(7,4) NOT NULL DEFAULT '-1.0000',
  `structurable` int NOT NULL DEFAULT '0',
  `hasPageImages` int NOT NULL DEFAULT '0',
  `commonGlyphs` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  `dropdownOrder` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UrlName` (`urlName`),
  KEY `structurable` (`structurable`),
  KEY `displayOrder` (`displayOrder`),
  KEY `dropdownOrder` (`dropdownOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `SourceAuthor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `SourceAuthor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sourceId` int NOT NULL DEFAULT '0',
  `rank` int NOT NULL DEFAULT '0',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `academicRank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `sourceRoleId` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sourceId` (`sourceId`,`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `SourceRole`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `SourceRole` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nameSingular` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `namePlural` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `priority` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `SourceType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `SourceType` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `displayOrder` int NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Subtitle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Subtitle` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `clipId` smallint unsigned DEFAULT NULL,
  `start` smallint unsigned DEFAULT NULL,
  `word` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clipId` (`clipId`),
  KEY `word` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci ROW_FORMAT=COMPRESSED;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Tag` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parentId` int NOT NULL DEFAULT '0',
  `value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `color` tinyint unsigned NOT NULL DEFAULT '0',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `iconOnly` int NOT NULL DEFAULT '0',
  `tooltip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `public` int NOT NULL DEFAULT '1',
  `isPos` tinyint(1) NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `value` (`value`),
  KEY `parentId` (`parentId`),
  KEY `isPos` (`isPos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TopEntry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TopEntry` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL DEFAULT '0',
  `hidden` tinyint NOT NULL DEFAULT '0',
  `manual` tinyint NOT NULL DEFAULT '0',
  `lastYear` tinyint NOT NULL DEFAULT '0',
  `numChars` int NOT NULL DEFAULT '0',
  `numDefs` int NOT NULL DEFAULT '0',
  `lastTimestamp` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TraineeSource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TraineeSource` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL DEFAULT '0',
  `sourceId` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`,`sourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Transform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Transform` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transfFrom` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `transfTo` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Tree` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `descriptionSort` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `status` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `description` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TreeEntry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TreeEntry` (
  `id` int NOT NULL AUTO_INCREMENT,
  `treeId` int NOT NULL DEFAULT '0',
  `entryId` int NOT NULL DEFAULT '0',
  `treeRank` int NOT NULL DEFAULT '0',
  `entryRank` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `treeId_2` (`treeId`,`entryRank`),
  KEY `entryId_2` (`entryId`,`treeRank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Typo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Typo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `definitionId` int NOT NULL DEFAULT '0',
  `problem` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `userName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `WordId` (`definitionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `User` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nick` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `detailsVisible` tinyint(1) DEFAULT NULL,
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `moderator` mediumint unsigned DEFAULT NULL,
  `preferences` int NOT NULL DEFAULT '0',
  `tabOrder` int NOT NULL DEFAULT '0',
  `widgetMask` int DEFAULT NULL,
  `widgetCount` int DEFAULT NULL,
  `medalMask` int DEFAULT NULL,
  `noAdsUntil` int NOT NULL DEFAULT '0',
  `anonymousDonor` int NOT NULL DEFAULT '0',
  `hasAvatar` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `Nick` (`nick`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `UserWordBookmark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `UserWordBookmark` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL DEFAULT '0',
  `definitionId` int NOT NULL DEFAULT '0',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Variable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Variable` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`),
  KEY `createDate` (`createDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `VideoClip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `VideoClip` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `videoId` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `Visual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Visual` (
  `id` int NOT NULL AUTO_INCREMENT,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `width` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `entryId` int NOT NULL DEFAULT '0',
  `userId` int NOT NULL DEFAULT '0',
  `revised` tinyint(1) DEFAULT '0',
  `createDate` int DEFAULT NULL,
  `modDate` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entryId` (`entryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `VisualTag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `VisualTag` (
  `id` int NOT NULL AUTO_INCREMENT,
  `imageId` int DEFAULT NULL,
  `entryId` int NOT NULL DEFAULT '0',
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `labelX` int NOT NULL DEFAULT '0',
  `labelY` int NOT NULL DEFAULT '0',
  `tipX` int NOT NULL DEFAULT '0',
  `tipY` int NOT NULL DEFAULT '0',
  `userId` int NOT NULL DEFAULT '0',
  `createDate` int DEFAULT NULL,
  `modDate` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entryId` (`entryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `WikiArticle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `WikiArticle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pageId` int NOT NULL DEFAULT '0',
  `revId` int NOT NULL DEFAULT '0',
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `section` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `fullUrl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `wikiContents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL,
  `htmlContents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL,
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pageId` (`pageId`),
  KEY `title` (`title`),
  KEY `modDate` (`modDate`),
  KEY `section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `WikiKeyword`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `WikiKeyword` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wikiArticleId` int NOT NULL DEFAULT '0',
  `keyword` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `wikiArticleId` (`wikiArticleId`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `WordOfTheDay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `WordOfTheDay` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL DEFAULT '0',
  `definitionId` int NOT NULL DEFAULT '0',
  `displayDate` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '0000-00-00',
  `priority` int NOT NULL DEFAULT '0',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `sponsor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  `modUserId` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `displayDate` (`displayDate`),
  KEY `definitionId` (`definitionId`),
  KEY `createDate` (`createDate`),
  KEY `modDate` (`modDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `updateWotd` BEFORE UPDATE ON `WordOfTheDay` FOR EACH ROW INSERT INTO WordOfTheDayHistory
SET wotdId = OLD.id,
userId = OLD.userId,
definitionId = OLD.definitionId,
displayDate = OLD.displayDate,
priority = OLD.priority,
image = OLD.image,
description = OLD.description,
url = OLD.url,
sponsor = OLD.sponsor,
modDate = OLD.modDate,
modUserId = OLD.modUserId,
actionUserId = NEW.modUserId */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `WordOfTheDayHistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `WordOfTheDayHistory` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wotdId` int NOT NULL DEFAULT '0',
  `userId` int NOT NULL DEFAULT '0',
  `definitionId` int NOT NULL DEFAULT '0',
  `displayDate` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '0000-00-00',
  `priority` int NOT NULL DEFAULT '0',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `sponsor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  `modUserId` int NOT NULL DEFAULT '0',
  `action` enum('UPDATE','DELETE') CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT 'UPDATE',
  `actionDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actionUserId` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `WordOfTheMonth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `WordOfTheMonth` (
  `id` int NOT NULL AUTO_INCREMENT,
  `displayDate` date DEFAULT NULL,
  `definitionId` int NOT NULL DEFAULT '0',
  `article` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT '',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `sponsor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `createDate` int NOT NULL DEFAULT '0',
  `modDate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `displayDate` (`displayDate`),
  KEY `createDate` (`createDate`),
  KEY `modDate` (`modDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `WotdArtist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `WotdArtist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  `userId` int DEFAULT NULL,
  `sponsor` int NOT NULL DEFAULT '0',
  `hidden` int NOT NULL DEFAULT '0',
  `credits` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `WotdAssignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `WotdAssignment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `artistId` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `date` (`date`),
  KEY `artistId` (`artistId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

