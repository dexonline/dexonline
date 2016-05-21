rename table MeaningTag to Tag;
alter table MeaningTagMap change meaningTagId tagId int not null;
