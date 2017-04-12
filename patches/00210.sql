alter table Donation
  add userId int not null after `date`,
  add index(userId);
