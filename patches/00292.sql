alter table Source change isActive hidden tinyint(1) not null default 0;

-- because "hidden" means the opposite of "isActive"
update Source set hidden = !hidden;

-- change the type of DELRIE and Epitete to specialized
update Source set type = 1 where id in (54, 71);

-- change the type of DEXLRA to Unofficial
update Source set type = 0 where id = 69;
