-- Dumped with command: mysqldump -u root -d DEX_dev > tools/schema.sql

-- MySQL dump 10.10
--
-- Host: localhost    Database: DEX_dev
-- ------------------------------------------------------
-- Server version	5.0.27

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Comment`
--

DROP TABLE IF EXISTS `Comment`;
CREATE TABLE `Comment` (
  `Id` int(11) NOT NULL auto_increment,
  `DefinitionId` int(11) NOT NULL default '0',
  `UserId` int(11) NOT NULL default '0',
  `Status` int(11) NOT NULL default '0',
  `Contents` text collate utf8_romanian_ci,
  `HtmlContents` text collate utf8_romanian_ci,
  PRIMARY KEY  (`Id`),
  KEY `DefId` (`DefinitionId`)
) ENGINE=MyISAM AUTO_INCREMENT=1069 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `Cookie`
--

DROP TABLE IF EXISTS `Cookie`;
CREATE TABLE `Cookie` (
  `Id` int(11) NOT NULL auto_increment,
  `CookieString` varchar(20) default NULL,
  `UserId` int(11) default NULL,
  `CreateDate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `cookieString` (`CookieString`)
) ENGINE=MyISAM AUTO_INCREMENT=11421 DEFAULT CHARSET=latin1;

--
-- Table structure for table `Definition`
--

DROP TABLE IF EXISTS `Definition`;
CREATE TABLE `Definition` (
  `Id` int(11) NOT NULL auto_increment,
  `UserId` int(11) NOT NULL default '0',
  `SourceId` int(11) NOT NULL default '0',
  `Lexicon` varchar(100) collate utf8_romanian_ci default NULL,
  `Displayed` int(11) NOT NULL default '0',
  `InternalRep` text collate utf8_romanian_ci,
  `HtmlRep` text collate utf8_romanian_ci,
  `Status` int(11) NOT NULL default '0',
  `CreateDate` int(11) NOT NULL default '0',
  `ModDate` int(11) NOT NULL default '0',
  UNIQUE KEY `Id` (`Id`),
  KEY `UserId` (`UserId`),
  KEY `CreateDate` (`CreateDate`),
  KEY `ModDate` (`ModDate`),
  KEY `Status` (`Status`),
  KEY `Lexicon` (`Lexicon`)
) ENGINE=MyISAM AUTO_INCREMENT=439553 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `GuideEntry`
--

DROP TABLE IF EXISTS `GuideEntry`;
CREATE TABLE `GuideEntry` (
  `Id` int(11) NOT NULL auto_increment,
  `Correct` text collate utf8_romanian_ci,
  `CorrectHtml` text collate utf8_romanian_ci,
  `Wrong` text collate utf8_romanian_ci,
  `WrongHtml` text collate utf8_romanian_ci,
  `Comments` text collate utf8_romanian_ci,
  `CommentsHtml` text collate utf8_romanian_ci,
  `Status` int(6) NOT NULL default '0',
  `CreateDate` int(11) NOT NULL default '0',
  `ModDate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `LexemDefinitionMap`
--

DROP TABLE IF EXISTS `LexemDefinitionMap`;
CREATE TABLE `LexemDefinitionMap` (
  `Id` int(11) NOT NULL auto_increment,
  `LexemId` int(11) NOT NULL,
  `DefinitionId` int(11) NOT NULL,
  UNIQUE KEY `Id` (`Id`),
  KEY `LexemId` (`LexemId`),
  KEY `DefinitionId` (`DefinitionId`)
) ENGINE=MyISAM AUTO_INCREMENT=351231 DEFAULT CHARSET=latin1;

--
-- Table structure for table `RecentLink`
--

DROP TABLE IF EXISTS `RecentLink`;
CREATE TABLE `RecentLink` (
  `Id` int(11) NOT NULL auto_increment,
  `UserId` int(11) NOT NULL,
  `VisitDate` int(11) NOT NULL,
  `Url` varchar(255) collate utf8_romanian_ci NOT NULL,
  `Text` varchar(255) collate utf8_romanian_ci NOT NULL,
  UNIQUE KEY `Id` (`Id`),
  KEY `UserId` (`UserId`)
) ENGINE=MyISAM AUTO_INCREMENT=15646 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `Source`
--

DROP TABLE IF EXISTS `Source`;
CREATE TABLE `Source` (
  `Id` int(11) NOT NULL auto_increment,
  `ShortName` varchar(40) collate utf8_romanian_ci default NULL,
  `Name` varchar(255) collate utf8_romanian_ci default NULL,
  `Author` varchar(255) collate utf8_romanian_ci default NULL,
  `Publisher` varchar(255) collate utf8_romanian_ci default NULL,
  `Year` varchar(255) collate utf8_romanian_ci default NULL,
  `CanContribute` tinyint(1) default NULL,
  `CanModerate` tinyint(1) default NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `Typo`
--

DROP TABLE IF EXISTS `Typo`;
CREATE TABLE `Typo` (
  `DefinitionId` int(11) NOT NULL default '0',
  `Problem` varchar(400) collate utf8_romanian_ci default NULL,
  KEY `WordId` (`DefinitionId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
  `Id` int(11) NOT NULL auto_increment,
  `Nick` varchar(100) collate utf8_romanian_ci default NULL,
  `Email` varchar(50) character set latin1 default NULL,
  `EmailVisible` tinyint(1) default NULL,
  `Password` varchar(32) character set latin1 default NULL,
  `Name` varchar(100) collate utf8_romanian_ci default NULL,
  `Moderator` tinyint(1) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `email` (`Email`),
  KEY `Nick` (`Nick`)
) ENGINE=MyISAM AUTO_INCREMENT=10875 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `changes`
--

DROP TABLE IF EXISTS `changes`;
CREATE TABLE `changes` (
  `id` int(11) NOT NULL auto_increment,
  `counter` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `diff` text collate utf8_romanian_ci,
  `createDate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20547 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `inflections`
--

DROP TABLE IF EXISTS `inflections`;
CREATE TABLE `inflections` (
  `infl_id` int(11) NOT NULL auto_increment,
  `infl_descr` char(255) collate utf8_romanian_ci NOT NULL,
  PRIMARY KEY  (`infl_id`)
) ENGINE=MyISAM AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `lexems`
--

DROP TABLE IF EXISTS `lexems`;
CREATE TABLE `lexems` (
  `lexem_id` int(11) NOT NULL auto_increment,
  `lexem_forma` char(50) collate utf8_romanian_ci default NULL,
  `lexem_neaccentuat` char(50) collate utf8_romanian_ci default NULL,
  `lexem_utf8_general` char(50) character set utf8 default NULL,
  `lexem_descr` varchar(255) collate utf8_romanian_ci NOT NULL,
  `lexem_invers` char(50) collate utf8_romanian_ci default NULL,
  `lexem_model` int(10) unsigned NOT NULL default '0',
  `lexem_restriction` char(4) collate utf8_romanian_ci default NULL,
  `lexem_is_loc` int(11) default NULL,
  PRIMARY KEY  (`lexem_id`),
  KEY `lexem_model` (`lexem_model`),
  KEY `lexem_forma` (`lexem_forma`),
  KEY `lex_restriction` (`lexem_restriction`),
  KEY `lexem_neaccentuat` (`lexem_neaccentuat`),
  KEY `lexem_invers` (`lexem_invers`),
  KEY `lexem_utf8_general` (`lexem_utf8_general`)
) ENGINE=MyISAM AUTO_INCREMENT=131607 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `model_description`
--

DROP TABLE IF EXISTS `model_description`;
CREATE TABLE `model_description` (
  `md_id` int(11) NOT NULL auto_increment,
  `md_model` int(10) unsigned NOT NULL default '0',
  `md_infl` int(10) unsigned NOT NULL default '0',
  `md_order` int(10) unsigned NOT NULL default '0',
  `md_transf` int(10) unsigned default NULL,
  PRIMARY KEY  (`md_id`),
  KEY `md_transf` (`md_transf`)
) ENGINE=MyISAM AUTO_INCREMENT=13977 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `model_types`
--

DROP TABLE IF EXISTS `model_types`;
CREATE TABLE `model_types` (
  `mt_id` int(11) NOT NULL auto_increment,
  `mt_value` char(2) collate utf8_romanian_ci NOT NULL default '',
  `mt_descr` char(255) collate utf8_romanian_ci default NULL,
  `mt_parent_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mt_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `models`
--

DROP TABLE IF EXISTS `models`;
CREATE TABLE `models` (
  `model_id` int(10) unsigned NOT NULL auto_increment,
  `model_type` char(2) collate utf8_romanian_ci NOT NULL default '',
  `model_no` int(10) unsigned NOT NULL,
  `model_descr` text collate utf8_romanian_ci,
  PRIMARY KEY  (`model_id`),
  KEY `morf_index` (`model_type`,`model_no`)
) ENGINE=MyISAM AUTO_INCREMENT=1058 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `transforms`
--

DROP TABLE IF EXISTS `transforms`;
CREATE TABLE `transforms` (
  `transf_id` int(11) NOT NULL auto_increment,
  `transf_from` char(16) collate utf8_romanian_ci default NULL,
  `transf_to` char(16) collate utf8_romanian_ci NOT NULL,
  `transf_descr` char(255) collate utf8_romanian_ci default NULL,
  PRIMARY KEY  (`transf_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1047 DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;

--
-- Table structure for table `wordlist`
--

DROP TABLE IF EXISTS `wordlist`;
CREATE TABLE `wordlist` (
  `wl_form` char(50) collate utf8_romanian_ci NOT NULL default '',
  `wl_utf8_general` char(50) character set utf8 default NULL,
  `wl_lexem` int(10) unsigned default NULL,
  `wl_analyse` int(10) unsigned default NULL,
  KEY `wl_lexem` (`wl_lexem`),
  KEY `wl_analyse` (`wl_analyse`),
  KEY `wl_form` (`wl_form`),
  KEY `wl_utf8_general` (`wl_utf8_general`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_romanian_ci;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-04-30 18:01:21
