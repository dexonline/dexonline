alter table Variable add id int not null auto_increment first, add primary key(id);
alter table Variable change Name name varchar(100) not null;
alter table Variable change Value value varchar(100) not null;
