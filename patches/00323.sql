alter table Tag
  add isPos tinyint(1) not null default 0 after public,
  add index(isPos);
