CREATE TABLE `DefinitionSimple` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `definitionId` int(11) NOT NULL,
  `definition` mediumtext NOT NULL,
  `createDate` int(11) NOT NULL,
  `modDate` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;