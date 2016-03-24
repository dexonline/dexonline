alter table Definition
  change structured structured tinyint(1) not null default 0,
  change status status int not null default 1;
