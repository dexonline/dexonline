alter table Typo
    add column userName varchar(100) not null after problem;
alter table User 
    change column preferences  preferences int(11) not null default '0' after moderator;
