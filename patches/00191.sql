alter table Inflection
  add gender int not null default 0 after rank,
  add number int not null default 0 after gender,
  add `case` int not null default 0 after number,
  add article int not null default 0 after `case`;

update Inflection set gender = 1 where description like '%masculin%';
update Inflection set gender = 1 where description like '%neutru%singular%';
update Inflection set gender = 2 where description like '%feminin%';
update Inflection set gender = 2 where description like '%neutru%plural%';

update Inflection set number = 1 where description like '%singular%';
update Inflection set number = 2 where description like '%plural%';

update Inflection set `case` = 1 where description like '%nominativ%';
update Inflection set `case` = 2 where description like '%dativ%';
update Inflection set `case` = 3 where description like '%vocativ%';

update Inflection set article = 1 where description like '%nearticulat%';
update Inflection set article = 2 where description like '% articulat%';
