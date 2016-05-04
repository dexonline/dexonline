drop table if exists AccuracyProject;
create table AccuracyProject (
  id int not null auto_increment,
  name varchar(255) not null,
  ownerId int not null,
  userId int not null,
  sourceId int not null,
  startDate date not null,
  endDate date not null,
  method int not null,
  createDate int not null,
  modDate int not null,

  primary key(id)
);

drop table if exists AccuracyRecord;
create table AccuracyRecord (
  id int not null auto_increment,
  projectId int not null,
  definitionId int not null,
  errors int not null,
  createDate int not null,
  modDate int not null,

  primary key(id),
  key(projectId),
  key(projectId, definitionId)
);
