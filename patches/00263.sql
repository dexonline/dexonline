alter table Abbreviation drop htmlRep;
alter table Definition drop htmlRep;
alter table DefinitionVersion drop htmlRep;
alter table Meaning drop htmlRep;
alter table WordOfTheDay drop htmlDescription;

drop table if exists Comment;
drop table if exists Footnote;
drop table if exists history_Comment;

drop trigger if exists updateDef;
create trigger updateDef before update on Definition
for each row
  insert into DefinitionVersion set
    definitionId = OLD.id,
    action = 0,
    sourceId = OLD.sourceId,
    lexicon = OLD.lexicon,
    internalRep = OLD.internalRep,
    status = OLD.status,
    createDate = OLD.modDate,
    modUserId = OLD.modUserId;
