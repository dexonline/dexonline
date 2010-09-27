-- Trigger DDL Statements
CREATE
DEFINER=`root`@`localhost`
TRIGGER `DEX`.`onWotdDelete`
AFTER DELETE ON `DEX`.`WordOfTheDay`
FOR EACH ROW
	delete from WordOfTheDayRel where wotdId = old.id;
