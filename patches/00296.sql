alter table Visual
  change path path varchar(255) not null default '',
  change entryId entryId int not null default 0,
  change userId userId int not null default 0;
