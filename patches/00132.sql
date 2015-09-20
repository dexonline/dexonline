drop table if exists WotdArtist;
create table WotdArtist (
  id int not null auto_increment,
  label varchar(255) not null,
  name varchar(255) not null,
  email varchar(255),
  credits text,

  primary key(id),
  unique key(label)
);
