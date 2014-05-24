drop table if exists LexemModel;
create table LexemModel (
  id int not null auto_increment,
  lexemId int not null,
  displayOrder int not null,
  modelType varchar(10) not null,
  modelNumber char(8) not null,
  restriction char(4) not null,
  tags varchar(255) not null,
  isLoc int not null,
  createDate int not null,
  modDate int not null,

  primary key(id),
  key(lexemId),
  key(lexemId, displayOrder),
  key(modelType)
);

insert into LexemModel
  (lexemId, displayOrder, modelType, modelNumber, restriction, tags, isLoc, createDate, modDate)
  select id, 1, modelType, modelNumber, restriction, tags, isLoc, unix_timestamp(), unix_timestamp() from Lexem;

alter table Lexem drop modelType, drop modelNumber, drop restriction, drop tags, drop isLoc;

alter table LexemSource change lexemId lexemModelId int;
update LexemSource set lexemModelId = (select LexemModel.id from LexemModel where LexemModel.lexemId = LexemSource.lexemModelId);

alter table FullTextIndex change lexemId lexemModelId int not null;
truncate table FullTextIndex;

alter table InflectedForm change lexemId lexemModelId int not null;
update InflectedForm set lexemModelId = (select LexemModel.id from LexemModel where LexemModel.lexemId = InflectedForm.lexemModelId);
