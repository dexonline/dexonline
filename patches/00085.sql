create table Synonym (
  id int not null auto_increment,
  meaningId int not null,
  lexemId int not null,
  createDate int not null,
  modDate int not null,
  primary key(id),
  key(meaningId),
  key(lexemId)
);
