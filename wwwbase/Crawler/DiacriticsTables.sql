
--
-- Table structure for table `Diacritics`
--

CREATE TABLE IF NOT EXISTS `Diacritics` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `before` varchar(10) NOT NULL,
  `middle` varchar(2) NOT NULL,
  `after` varchar(10) NOT NULL,
  `defaultForm` int(11) NOT NULL,
  `curvedForm` int(11) NOT NULL,
  `circumflexForm` int(11) NOT NULL,
  `createDate` int(11) DEFAULT NULL,
  `modDate` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Table structure for table `FilesUsedInDiacritics`
--

CREATE TABLE IF NOT EXISTS `FilesUsedInDiacritics` (
  `fileId` int(11) NOT NULL,
  `createDate` int(11) DEFAULT NULL,
  `modDate` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
