rename table usr to User;
alter table User change id Id int(11) not null auto_increment;
alter table User change nick Nick varchar(20);
alter table User change email Email varchar(50);
alter table User change email_visible EmailVisible bool;
alter table User change password Password varchar(32);
alter table User change name Name varchar(50);
alter table User change moderator Moderator bool;
alter table User add unique index(Nick);

rename table words to Word;
alter table Word change counter Counter int not null auto_increment;
alter table Word change name Name varchar(30);
alter table Word change dname Dname varchar(40);
alter table Word change lexicon Lexicon varchar(30) character set latin1 collate latin1_bin;
alter table Word change superscript Superscript varchar(10);
alter table Word change def Def text;
alter table Word change htmlDef HtmlDef text;
alter table Word change uid UserId int;
alter table Word change sourceId SourceId int;
alter table Word change displayed Displayed int not null default 0;
alter table Word change createDate CreateDate int not null default 0;
alter table Word change modDate ModDate int not null default 0;
alter table Word change status Status int not null default 0;

rename table typos to Typo;
alter table Typo change counter WordId int not null default 0;
alter table Typo change problem Problem varchar(200);
alter table Typo drop index `counter`;
alter table Typo add index(WordId);

rename table cookie to Cookie;
alter table Cookie change id Id int(11) not null auto_increment;
alter table Cookie change cookieString CookieString varchar(20);
alter table Cookie change userId UserId int;
alter table Cookie change createDate CreateDate int not null default 0;
