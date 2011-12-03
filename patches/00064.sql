alter table User
  change emailVisible detailsVisible tinyint(1) default null,
  add identity varchar(255) after id,
  add unique key(identity);
