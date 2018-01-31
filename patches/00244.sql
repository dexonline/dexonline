alter table User
  change email email varchar(255) collate utf8_general_ci;
alter table User
  add anonymousDonor int not null default 0 after noAdsUntil;
