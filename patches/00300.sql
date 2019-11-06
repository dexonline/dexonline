insert into Variable (name, value, createDate, modDate)
  values ('Count.entriesWithMultipleMainLexemes', 0, UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW()));
alter table Entry
	add column modUserId int not null default '0' after modDate;
