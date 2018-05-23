delete from WordOfTheDay where displayDate = '0000-00-00';
update WordOfTheDay set displayDate = '0000-00-00' where displayDate is null;

alter table WordOfTheDay
  change displayDate displayDate char(10) not null default '0000-00-00',
  add definitionId int not null default 0 after userId,
  add createDate int not null,
  add modDate int not null,
  add index(definitionId),
  add index(createDate),
  add index(modDate);

update WordOfTheDay
  set createDate = unix_timestamp(creationDate),
    modDate = unix_timestamp(creationDate);

alter table WordOfTheDay
  drop creationDate;

update WordOfTheDayRel r
  join WordOfTheDay w on r.wotdId = w.id
  set w.definitionId = r.refId;

drop table WordOfTheDayRel;
drop trigger if exists onWotdDelete;

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
