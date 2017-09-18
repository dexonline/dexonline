create table CrawlerIgnoredUrl (
  id int not null auto_increment,
  url varchar(500) not null,
  failureCount int not null,
  createDate int not null,
  modDate int not null,

  primary key(id),
  key(url(200))
);
