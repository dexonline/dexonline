create table PasswordToken (
  id int not null auto_increment,
  userId int not null,
  token varchar(50) not null,
  createDate int not null,
  primary key(id),
  key(token)
);
