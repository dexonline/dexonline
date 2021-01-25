drop table if exists MillData;

create table MillData (
  id int not null auto_increment,
  meaningId int not null default 0,
  word varchar(100) not null default '',
  posMask tinyint not null default 0,
  internalRep longtext not null default '',

  shown int not null default 0,
  guessed int not null default 0,
  ratio float not null default 0.0,

  createDate int not null default 0,
  modDate int not null default 0,

  primary key(id),
  unique key(meaningId),
  key(word),
  key(ratio),
  key(modDate)
);
