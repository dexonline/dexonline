-- see TopEntry.php for field information
create table TopEntry (
  id int not null auto_increment,
  userId int not null default 0,

  hidden tinyint not null default 0,
  manual tinyint not null default 0,
  lastYear tinyint not null default 0,

  numChars int not null default 0,
  numDefs int not null default 0,

  lastTimestamp int not null default 0,

  primary key (id)
);
