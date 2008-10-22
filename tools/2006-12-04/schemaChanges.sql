drop table if exists ModelType;
create table ModelType (
  Id int not null auto_increment,
  Value varchar(2) not null,
  Description varchar(255) not null,
  ParentId int,
  unique key (Id),
  unique key (Value)
);
