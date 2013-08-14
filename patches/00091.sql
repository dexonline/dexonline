create table LexemSource (
  id int not null auto_increment,
  lexemId int not null,
  sourceId int not null,
  createDate int not null,
  modDate int not null,
  primary key(id)
);
