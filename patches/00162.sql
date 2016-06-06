create table EntryDefinition (
  id int not null auto_increment,
  entryId int not null,
  definitionId int not null,
  createDate int not null,
  modDate int not null,
  primary key (id),
  key (entryId),
  key (definitionId)
);

insert into EntryDefinition
  select 0, l.entryId, ldm.definitionId, unix_timestamp(), unix_timestamp()
  from LexemDefinitionMap ldm
  join Lexem l on l.id = ldm.lexemId;

drop table LexemDefinitionMap;
