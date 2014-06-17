alter table Definition drop displayed;

drop trigger if exists updateDef;

create trigger updateDef before update on Definition
for each row
  insert into history_Definition set
    id = OLD.id,
    action = 'UPDATE',
    user = USER(),
    userId = OLD.userId, 
    sourceId = OLD.sourceId,
    lexicon = OLD.lexicon,
    internalRep = OLD.internalRep,
    htmlRep = OLD.htmlRep,
    status = OLD.status,
    createDate = OLD.createDate,
    modDate = OLD.modDate,
    newDate = NEW.modDate,
    modUserId = OLD.modUserId;

drop trigger if exists deleteDef;

create trigger deleteDef before delete on Definition
for each row
  insert into history_Definition SET 
    id = OLD.Id,
    action = 'DELETE',
    user = USER(),
    userId = OLD.UserId, 
    sourceId = OLD.SourceId,
    lexicon = OLD.Lexicon,
    internalRep = OLD.InternalRep,
    htmlRep = OLD.HtmlRep,
    status = OLD.Status,
    createDate = OLD.CreateDate,
    modDate = OLD.ModDate,
    newDate = unix_timestamp(),
    modUserId = OLD.ModUserId;
