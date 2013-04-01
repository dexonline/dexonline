create table Meaning (
  id int not null auto_increment,
  parentId int not null,
  userId int not null,
  lexemId int not null,
  internalRep mediumtext,
  htmlRep mediumtext,
  internalComment mediumText,
  htmlComment mediumText,
  status int not null,
  createDate int not null,
  modDate int not null,
  primary key(id),
  key(lexemId),
  key(status)
) collate utf8_romanian_ci;

create table MeaningSource (
  id int not null auto_increment,
  meaningId int not null,
  sourceId int not null,
  createDate int not null,
  modDate int not null,
  primary key(id),
  key(meaningId)
) collate utf8_romanian_ci;
