create table Footnote (
  id int not null auto_increment,
  definitionId int not null,
  userId int not null,
  rank int not null,
  htmlRep text,
  createDate int not null,
  modDate int not null,
  primary key (id),
  key (definitionId)
);
