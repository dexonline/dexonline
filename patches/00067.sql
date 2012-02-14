alter table WordOfTheDay add image varchar(255) default null after priority;

delete from WordOfTheDayRel where refId = 0;
