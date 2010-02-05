create table WordForm (
  Id int not null auto_increment,
  DeclensionModelId int not null,
  DeclensionId int not null,
  Form varchar(100) not null,
  IsException bool not null,
  unique key (Id),
  key (DeclensionModelId),
  key (DeclensionId),
  key (Form)
);

create table DeclensionModel (
  Id int not null auto_increment,
  WordId int not null,
  PartOfSpeechId int not null,
  BaseForm varchar(100) not null,
  unique key (Id),
  key (WordId),
  key (PartOfSpeechId)
);
