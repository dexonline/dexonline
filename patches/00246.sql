update Abbreviation set ambiguous=1 where short like '*%';
update Abbreviation set short=mid(short,2,1000) where short like '*%';
update Abbreviation set caseSensitive=0 where caseSensitive is null;
alter table Abbreviation
	change column `long` `long` varchar(1000) null default null collate 'utf8_romanian_ci' after short,
	change column ambiguous ambiguous tinyint(1) not null default '0' after `long`,
	change column caseSensitive caseSensitive tinyint(1) not null default '0' after ambiguous;
alter table Abbreviation
	add column enforced tinyint(1) not null default '0' after `long`;
