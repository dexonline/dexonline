-- a packed integer storing a permutation of {0, 1, 2, ... }, four bits each
-- or zero for the identical permutation (i.e. preference not set)
alter table User
  add tabOrder int not null default 0 after preferredTab;

update User
  set tabOrder = (1 << 16) + (0 << 12) + (2 << 8) + (3 << 4) + (4 << 0)
  where preferredTab = 1;

update User
  set tabOrder = (2 << 16) + (0 << 12) + (1 << 8) + (3 << 4) + (4 << 0)
  where preferredTab = 2;

alter table User
  drop preferredTab;
