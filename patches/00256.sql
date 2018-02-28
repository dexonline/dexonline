alter table CrawlerUrl
  add extractedUnknownWords int not null default 0 after title,
  add key(extractedUnknownWords);

drop table if exists CrawlerUnknownWord;
create table CrawlerUnknownWord (
  id int not null auto_increment,
  word varchar(255) not null collate utf8_romanian_ci,
  crawlerUrlId int not null,
  position int not null,

  createDate int not null,
  modDate int not null,

  primary key(id),
  key(word)
);

drop table if exists CrawlerPhrase;
