CREATE TABLE `Curator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sourceId` INT(11) NOT NULL,
  `userId` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE UNIQUE INDEX idx_unq_curator_source_user ON Curator(sourceId, userId);
CREATE INDEX IF NOT EXISTS idx_user_moderator ON User(moderator);
