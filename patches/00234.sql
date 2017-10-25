create table if not exists PageIndex (
  id int not null auto_increment,
  sourceId int not null default 0,
  volume int not null default 0,
  page int not null default 0,
  word varchar(255) collate utf8_romanian_ci not null default '',
  number int not null default 0,

  primary key(id),
  key(sourceId, volume, page),
  key(sourceId, word)
);

alter table Source
  add hasPagePdfs int not null default 0 after structurable;
