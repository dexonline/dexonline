create table if not exists ObjectTag (
  id int not null auto_increment,
  objectId int not null,
  objectType int not null,
  tagId int not null,
  createDate int not null,
  modDate int not null,

  primary key(id),
  key(objectId, objectType),
  key(tagId)
);

insert into ObjectTag (objectId, objectType, tagId, createDate, modDate)
  select definitionId, 1, tagId, createDate, modDate from DefinitionTag;

insert into ObjectTag (objectId, objectType, tagId, createDate, modDate)
  select lexemId, 2, tagId, createDate, modDate from LexemTag;

insert into ObjectTag (objectId, objectType, tagId, createDate, modDate)
  select meaningId, 3, tagId, createDate, modDate from MeaningTag;

drop table if exists DefinitionTag;
drop table if exists LexemTag;
drop table if exists MeaningTag;
