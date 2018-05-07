alter table Definition
  add volume int not null default 0 after structured,
  add page int not null default 0 after volume,
  add index(volume, page);
