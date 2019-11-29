alter table `pageIndex`
  add column `createDate` int(11) not null after `number`,
  add column `modDate` int(11) not null after `createDate`,
  add column `modUserId` int(11) not null default '0' after `modDate`;