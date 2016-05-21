create table Entry (
  id int not null auto_increment,
  description varchar(255) not null default '',
  createDate int not null,
  modDate int not null,

  primary key (id),
  key (description)
);
