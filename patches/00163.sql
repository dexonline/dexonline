create table DefinitionTag (
  id int(11) not null auto_increment,
  definitionId int(11) not null,
  tagId int(11) not null,
  createDate int(11) not null,
  modDate int(11) not null,
  primary key (id),
  key (definitionId),
  key (tagId)
);
