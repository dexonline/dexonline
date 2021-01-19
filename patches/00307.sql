alter table User
  add preferredTab int not null default 0 after preferences;

update User
  set preferredTab = 1
  where preferences & 0x10;

-- clear the unused 0x100, 0x20 and 0x10 bits
update User
  set preferences = preferences & ~0x130;
