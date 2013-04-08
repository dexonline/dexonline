create table MeaningTag (
  id int not null auto_increment,
  value varchar(255) not null,
  createDate int not null,
  modDate int not null,
  primary key(id),
  key(value)
) collate utf8_romanian_ci;

create table MeaningTagMap (
  id int not null auto_increment,
  meaningId int not null,
  meaningTagId int not null,
  createDate int not null,
  modDate int not null,
  primary key(id),
  key(meaningId),
  key(meaningTagId)
);
