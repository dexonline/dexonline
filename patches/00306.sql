DROP TABLE history_WordOfTheDay;

DROP TABLE history_WordOfTheDayRel;

ALTER TABLE WordOfTheDay ADD COLUMN modUserId int(11) NOT NULL;

CREATE TABLE WordOfTheDayHistory (
  id int(11) NOT NULL AUTO_INCREMENT,
  wotdId int(11) NOT NULL,
  userId int(11) NOT NULL,
  definitionId int(11) NOT NULL DEFAULT '0',
  displayDate char(10) COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '0000-00-00',
  priority int(11) NOT NULL DEFAULT '0',
  image varchar(255) COLLATE utf8mb4_romanian_ci DEFAULT NULL,
  description mediumtext COLLATE utf8mb4_romanian_ci NOT NULL,
  url varchar(255) COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  sponsor varchar(255) COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '',
  createDate int(11) NOT NULL,
  modDate int(11) NOT NULL,
  modUserId int(11) NOT NULL,
  action enum('UPDATE','DELETE') DEFAULT 'UPDATE',
  actionDate timestamp DEFAULT CURRENT_TIMESTAMP,
  actionUserId int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;


CREATE TRIGGER updateWotd
BEFORE UPDATE
ON WordOfTheDay
FOR EACH ROW
INSERT INTO WordOfTheDayHistory
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
actionUserId = NEW.modUserId;

