ALTER TABLE Definition ADD COLUMN ModUserId INT NOT NULL DEFAULT 0;
CREATE TABLE history_Definition like Definition;

ALTER TABLE history_Definition DROP COLUMN Id;
ALTER TABLE history_Definition ADD COLUMN Id INT NOT NULL FIRST, ADD COLUMN NewDate INT NOT NULL AFTER ModDate;
ALTER TABLE history_Definition ADD COLUMN Version INT NOT NULL auto_increment AFTER Id, ADD INDEX Id_Version(Id, Version);
ALTER TABLE history_Definition ADD COLUMN Action ENUM('UPDATE','INSERT','DELETE') AFTER Version, ADD COLUMN User VARCHAR(25) AFTER Action;

DELIMITER |
CREATE TRIGGER updateDef AFTER UPDATE ON Definition
FOR EACH ROW BEGIN
INSERT INTO history_Definition SET 
Id = OLD.Id,
Action = 'UPDATE',
User = USER(),
UserId = OLD.UserId, 
SourceId = OLD.SourceId,
Lexicon = OLD.Lexicon,
Displayed = OLD.Displayed,
InternalRep = OLD.InternalRep,
HtmlRep = OLD.HtmlRep,
Status = OLD.Status,
CreateDate = OLD.CreateDate,
ModDate = OLD.ModDate,
NewDate = NEW.ModDate,
ModUserId = OLD.ModUserId;
END;
|
DELIMITER ;

DELIMITER |
CREATE TRIGGER deleteDef BEFORE DELETE ON Definition
FOR EACH ROW BEGIN
INSERT INTO history_Definition SET 
Id = OLD.Id,
Action = 'DELETE',
User = USER(),
UserId = OLD.UserId, 
SourceId = OLD.SourceId,
Lexicon = OLD.Lexicon,
Displayed = OLD.Displayed,
InternalRep = OLD.InternalRep,
HtmlRep = OLD.HtmlRep,
Status = OLD.Status,
CreateDate = OLD.CreateDate,
ModDate = OLD.ModDate,
NewDate = UNIX_TIMESTAMP(),
ModUserId = OLD.ModUserId
;
END;
|
DELIMITER ;
