create table if not exists Mention (
  id int not null auto_increment,
  meaningId int not null,
  objectId int not null,
  objectType int not null,
  createDate int not null,
  modDate int not null,

  primary key(id),
  key(meaningId),
  key(objectId, objectType)
);
