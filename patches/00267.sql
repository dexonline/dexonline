delete from WordOfTheDay where displayDate = '0000-00-00';
update WordOfTheDay set displayDate = '0000-00-00' where displayDate is null;

alter table WordOfTheDay
  change displayDate displayDate char(10) not null default '0000-00-00',
  add createDate int not null,
  add modDate int not null,
  add index(createDate),
  add index(modDate);

update WordOfTheDay
  set createDate = unix_timestamp(creationDate),
    modDate = unix_timestamp(creationDate);

alter table WordOfTheDay
  drop creationDate;

alter table WordOfTheDayRel
  add createDate int not null,
  add modDate int not null,
  add index(createDate),
  add index(modDate),
  add index(refId);

alter table WordOfTheMonth
  add createDate int not null,
  add modDate int not null,
  add index(createDate),
  add index(modDate);

update WordOfTheMonth
  set createDate = unix_timestamp(creationDate),
    modDate = unix_timestamp(creationDate);

alter table WordOfTheMonth
  drop creationDate;
