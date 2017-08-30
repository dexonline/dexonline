create table CrawlerUrl (
  id int not null auto_increment,
  url varchar(500) not null,
  author varchar(200) not null,
  title varchar(200) not null,
  createDate int not null,
  modDate int not null,

  primary key(id),
  key(url(200))
);
