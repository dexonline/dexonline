delete from Abbreviation;
alter table Abbreviation
	change column `long` internalRep varchar(1000) null default null collate 'utf8_romanian_ci' after short;
alter table Abbreviation
	change column ambiguous ambiguous tinyint(1) not null default '0' after internalRep,
	change column caseSensitive caseSensitive tinyint(1) not null default '0' after ambiguous,
	add column enforced tinyint(1) not null default '0' after caseSensitive,
	add column htmlRep varchar(1000) null default null after internalRep;
