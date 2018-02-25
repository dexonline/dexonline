create table CrawlerPhrase (
  id int not null auto_increment,
  crawlerUrlId int not null,
  contents text not null,
  createDate int not null,
  modDate int not null,

  primary key(id),
  key(crawlerUrlId)
);
