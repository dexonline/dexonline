create table Tree (
  id int not null auto_increment,
  description varchar(255) not null default '',
  createDate int not null,
  modDate int not null,

  primary key (id),
  key (description)
);

create table TreeEntry (
  id int not null auto_increment,
  treeId int not null,
  entryId int not null,
  createDate int not null,
  modDate int not null,
  primary key (id),
  key (treeId),
  key (entryId)
);

alter table Meaning
  add treeId int not null after lexemId,
  add key(treeId);
