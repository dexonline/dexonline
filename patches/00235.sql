drop table WikiSection;

alter table WikiArticle
  add section varchar(255) not null default '' after title,
  add key(section);
