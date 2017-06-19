drop table if exists DefinitionVersion;
rename table history_Definition to DefinitionVersion;

alter table DefinitionVersion
  change Id definitionId int not null,
  change Version Version int not null,
  change Action actionEnum enum('UPDATE','INSERT','DELETE'),
  add action int not null after actionEnum,
  change SourceId sourceId int not null,
  change Lexicon lexicon varchar(100) not null,
  change InternalRep internalRep mediumtext,
  change HtmlRep htmlRep mediumtext,
  change Status status int not null,
  change CreateDate createDate int not null,
  change ModUserId modUserId int not null;

update DefinitionVersion
  set action = if (actionEnum = 'UPDATE', 0, 1),
      createDate = ModDate;

alter table DefinitionVersion
  add id int primary key auto_increment first,
  drop Version,
  drop actionEnum,
  drop User,
  drop UserId,
  drop Displayed,
  drop ModDate,
  drop NewDate,
  drop index `Status`,
  drop index `CreateDate`,
  drop index `Lexicon`,
  drop index `Id_Version`,
  add index(definitionId);

drop trigger if exists updateDef;
create trigger updateDef before update on Definition
for each row
  insert into DefinitionVersion set
    definitionId = OLD.id,
    action = 0,
    sourceId = OLD.sourceId,
    lexicon = OLD.lexicon,
    internalRep = OLD.internalRep,
    htmlRep = OLD.htmlRep,
    status = OLD.status,
    createDate = OLD.modDate,
    modUserId = OLD.modUserId;

drop trigger if exists deleteDef;
