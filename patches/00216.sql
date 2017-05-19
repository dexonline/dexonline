alter table Source
  add dropdownOrder int not null,
  add key(displayOrder),
  add key(dropdownOrder);

update Source set dropdownOrder = 5 where urlName = 'dex09';
update Source set dropdownOrder = 4 where urlName = 'doom2';
update Source set dropdownOrder = 3 where urlName = 'sinonime';
update Source set dropdownOrder = 2 where urlName = 'das';
update Source set dropdownOrder = 1 where urlName = 'antonime';
