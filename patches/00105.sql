alter table Definition add column similarSource tinyint(1) NOT NULL default 0 after sourceId;

CREATE TABLE `SimilarSource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sourceId` int(11) NOT NULL,
  `similarSource` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sourceId` (`sourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf;

insert into SimilarSource(sourceId, similarSource) values (1,2), (2,3), (3,4), (8,18), (19,18), (27,1), (40,27), (21,17);

