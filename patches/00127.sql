alter table User
  add openidConnectSub varchar(255) after identity,
  add index(openidConnectSub);
