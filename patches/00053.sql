alter table GuideEntry add column userId int;
alter table GuideEntry add column modUserId int;
alter table WordOfTheDay drop index displayDate;
alter table WordOfTheDay add unique index displayDate (displayDate);
