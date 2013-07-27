SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


--
-- Table structure for table `Link`
--

CREATE TABLE IF NOT EXISTS `Link` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `canonicalUrl` varchar(256) NOT NULL,
  `domain` varchar(128) NOT NULL,
  `urlHash` varchar(40) NOT NULL,
  `crawledPageId` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=684 ;

--
-- Table structure for table `CrawledPage`
--

CREATE TABLE IF NOT EXISTS `CrawledPage` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `url` varchar(256) NOT NULL,
  `httpStatus` int(11) NOT NULL,
  `rawPagePath` varchar(128) NOT NULL,
  `ParsedTextPath` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;
