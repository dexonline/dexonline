create table if not exists SourceAuthor (
  id int not null auto_increment,
  sourceId int not null,
  rank int not null default 0,

  title varchar(100) not null default '',
  name varchar(100) not null default '',
  academicRank varchar(100) not null default '',
  sourceRoleId int not null default 0,

  createDate int not null default 0,
  modDate int not null default 0,

  primary key(id),
  key(sourceId, rank)
);

create table if not exists SourceRole (
  id int not null auto_increment,

  nameSingular varchar(100) not null default '',
  namePlural varchar(100) not null default '',
  priority int not null default 0,

  createDate int not null default 0,
  modDate int not null default 0,

  primary key(id)
);
