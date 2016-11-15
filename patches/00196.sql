alter table Source
  add createDate int not null default 0,
  add modDate int not null default 0;

update Source set createDate = unix_timestamp(), modDate = unix_timestamp();
