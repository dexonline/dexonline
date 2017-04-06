create table if not exists Donation (
  id int not null auto_increment,
  email varchar(255) not null,
  amount int not null,
  `date` date not null,
  source int not null,
  emailSent int not null,
  createDate int not null,
  modDate int not null,

  primary key(id)
);
