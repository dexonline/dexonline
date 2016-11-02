alter table Source
  add courtesyLink varchar(255) not null default '' after link,
  add courtesyText varchar(255) not null default '' after courtesyLink;
