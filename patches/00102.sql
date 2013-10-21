create table if not exists CrawledPage (
  id int not null auto_increment,
  timestamp int not null,
  url varchar(255) not null,
  httpStatus int not null,
  rawPagePath varchar(255) not null,
  parsedTextPath varchar(255) not null,

  primary key (id),
  key(httpStatus)
);

create table if not exists Link(
  id int not null auto_increment,
  canonicalUrl varchar(255) not null,
  domain varchar(255) not null,
  crawledPageId int not null,

  primary key(id),
  key(domain),
  key(crawledPageId)
);
