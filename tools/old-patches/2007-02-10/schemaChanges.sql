drop table if exists inflections;
create table inflections (
  infl_id int not null auto_increment,
  infl_descr varchar(255) not null,
  unique key (infl_id)
);

drop table if exists ModelType;
drop table if exists model_types;
create table model_types (
  mt_id int not null auto_increment,
  mt_value varchar(2) not null,
  mt_descr varchar(255) not null,
  mt_parent_id int,
  unique key (mt_id),
  unique key (mt_value)
);
