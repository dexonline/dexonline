alter table WordOfTheDay
  add htmlDescription text not null after description;

update WordOfTheDay set htmlDescription = description;
