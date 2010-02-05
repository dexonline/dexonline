DROP TRIGGER updateDef;
CREATE TABLE history_Comment like Comment;

ALTER TABLE history_Comment DROP COLUMN Id;
ALTER TABLE history_Comment ADD COLUMN Id INT NOT NULL FIRST, ADD COLUMN NewDate INT NOT NULL AFTER HtmlContents;
ALTER TABLE history_Comment ADD COLUMN Version INT NOT NULL auto_increment AFTER Id, ADD INDEX Id_Version(Id, Version);
ALTER TABLE history_Comment ADD COLUMN Action ENUM('UPDATE','INSERT','DELETE') AFTER Version, ADD COLUMN User VARCHAR(25) AFTER Action;

DELIMITER |
CREATE TRIGGER updateDef AFTER UPDATE ON Definition
FOR EACH ROW BEGIN
IF OLD.Displayed = NEW.Displayed THEN
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
END IF;
END;
|
DELIMITER ;

DELIMITER |
CREATE TRIGGER updateComment AFTER UPDATE ON Comment
FOR EACH ROW BEGIN
INSERT INTO history_Comment SET 
Id = OLD.Id,
Action = 'UPDATE',
User = USER(),
UserId = OLD.UserId, 
DefinitionId = OLD.DefinitionId,
Status = OLD.Status,
Contents = OLD.Contents,
HtmlContents = OLD.HtmlContents,
NewDate = UNIX_TIMESTAMP();
END;
|
DELIMITER ;

DELIMITER |
CREATE TRIGGER deleteComment BEFORE DELETE ON Comment
FOR EACH ROW BEGIN
INSERT INTO history_Comment SET 
Id = OLD.Id,
Action = 'DELETE',
User = USER(),
UserId = OLD.UserId, 
DefinitionId = OLD.DefinitionId,
Status = OLD.Status,
Contents = OLD.Contents,
HtmlContents = OLD.HtmlContents,
NewDate = UNIX_TIMESTAMP();
END;
|
DELIMITER ;
