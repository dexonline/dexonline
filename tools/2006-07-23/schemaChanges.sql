create table PartOfSpeech (
  Id int not null auto_increment,
  Name varchar(80) not null,
  DisplayName varchar(80) not null,
  Position int not null,
  unique key (Id),
  key (Position)
);

create table Declension (
  Id int not null auto_increment,
  PartOfSpeechId int not null,
  InflectionId int not null,
  Name varchar (80) not null,
  Position int not null,
  unique key (Id),
  key (PartOfSpeechId),
  key (Position)  
);
