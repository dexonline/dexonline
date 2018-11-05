alter table Lexeme drop isLoc;
alter table ModelDescription drop isLoc;

create table Loc (
  id int not null auto_increment,
  version varchar(10) not null,
  form varchar(20) not null,

  primary key(id),
  key(version, form)
);
