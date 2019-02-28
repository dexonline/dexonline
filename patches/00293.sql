alter table WordOfTheDay
  change description description mediumtext not null default '',
  change url url varchar(255) not null default '',
  change sponsor sponsor varchar(255) not null default '';
