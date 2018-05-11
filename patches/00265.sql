create table HarmonizeTag (
  id int not null auto_increment,
  modelType varchar(10) not null,
  modelNumber varchar(8) not null,
  tagId int not null,

  createDate int not null,
  modDate int not null,

  primary key (id),
  key (tagId),
  key (modelType),
  key (modelType, modelNumber)
);

create table HarmonizeModel (
  id int not null auto_increment,
  modelType varchar(10) not null,
  modelNumber varchar(8) not null,
  tagId int not null,
  newModelType varchar(10) not null,
  newModelNumber varchar(8) not null,

  createDate int not null,
  modDate int not null,

  primary key (id),
  key (tagId),
  key (modelType),
  key (modelType, modelNumber)
);
