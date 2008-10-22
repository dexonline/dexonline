alter table lexems add ModDate int not null, add CreateDate int not null;
alter table lexems add key (ModDate);
update lexems set CreateDate = unix_timestamp(), ModDate = unix_timestamp();
