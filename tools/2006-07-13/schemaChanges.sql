create table Morph (
  Id int not null auto_increment,
  Symbol varchar(10) not null,
  Normal varchar(10) not null,
  Applied varchar(10) not null,
  unique key (Id),
  key (Symbol)
);

create table Inflection (
  Id int not null auto_increment,
  Name varchar(255) not null,
  Position int not null,
  Morphs varchar(100) not null,
  unique key (Id),
  key (Position)
);

create table SuffixApplication (
  Id int not null auto_increment,
  InflectionId int not null,
  Position int not null,
  Suffix varchar(40) not null,
  Remove varchar(40) not null,
  Append varchar(40) not null,
  unique key(Id),
  key (InflectionId),
  key (Position)
);
