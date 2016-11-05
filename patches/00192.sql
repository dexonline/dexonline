alter table Lexem add compound int not null default 0 after modDate;

create table if not exists Fragment (
  id int not null auto_increment,
  lexemId int not null,
  partId int not null,
  capitalized int not null,
  declension int not null,
  rank int not null,

  createDate int not null,
  modDate int not null,

  primary key(id),
  key(lexemId),
  key(partId)
);

insert into ConstraintMap
  select 'A', id, -1
  from Inflection
  where description like '%nearticulat%';
