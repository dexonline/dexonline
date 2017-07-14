alter table Tag
  add color varchar(10) not null after value,
  add background varchar(10) not null after color;
