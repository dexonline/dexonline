update Source set type = 1 where type = 2;
alter table Source change type normative tinyint(1) not null default 0;
