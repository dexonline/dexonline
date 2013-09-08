CREATE TABLE IF NOT EXISTS `CrawledPage` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `url` varchar(256) NOT NULL,
  `httpStatus` int(11) NOT NULL,
  `rawPagePath` varchar(128) NOT NULL,
  `parsedTextPath` varchar(128) NOT NULL,
  `createDate` int(4) DEFAULT NULL,
  `modDate` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;



CREATE TABLE IF NOT EXISTS `Link` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `canonicalUrl` varchar(256) NOT NULL,
  `domain` varchar(128) NOT NULL,
  `crawledPageId` bigint(20) NOT NULL,
  `createDate` int(4) DEFAULT NULL,
  `modDate` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


