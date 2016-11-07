alter table Variable
  add createDate int not null,
  add modDate int not null,
  add key(createDate);

update Variable
  set createDate = unix_timestamp(), modDate = unix_timestamp();
