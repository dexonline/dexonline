alter table Source add column link varchar(255) COLLATE utf8_romanian_ci default null after `year`, add column isActive tinyint(1) default 1 after `year`;
