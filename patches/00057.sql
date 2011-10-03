create table WikiArticle (
  id int not null auto_increment,
  pageId int not null,
  revId int not null,
  title varchar(255) not null,
  fullUrl varchar(255) not null,
  wikiContents mediumtext not null,
  htmlContents mediumtext not null,
  createDate int not null,
  modDate int not null,
  primary key(id),
  unique key(pageId),
  key(title),
  key(modDate)
);

create table WikiKeyword (
  id int not null auto_increment,
  wikiArticleId int not null,
  keyword varchar(255) not null,
  primary key(id),
  key (wikiArticleId),
  key (keyword)
);
