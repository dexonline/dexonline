alter table Cookie change Id id int not null auto_increment;
alter table Cookie change CookieString cookieString varchar(20);
alter table Cookie change UserId userId int;
alter table Cookie change CreateDate createDate int not null;

alter table User change Id id int not null auto_increment;
alter table User change Nick nick varchar(100);
alter table User change Email email varchar(50);
alter table User change EmailVisible emailVisible tinyint(1);
alter table User change Password password varchar(32);
alter table User change Name name varchar(100);
alter table User change Moderator moderator tinyint(1);
alter table User change Preferences preferences set('CEDILLA_BELOW','FORCE_DIACRITICS','OLD_ORTHOGRAPHY','EXCLUDE_UNOFFICIAL','SHOW_PARADIGM');
