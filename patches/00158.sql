alter table Lexem
  add modelType varchar(10) not null default '' after modDate,
  add modelNumber char(8) not null default '' after modelType,
  add restriction char(10) not null default '' after modelNumber,
  add notes varchar(255) not null default '' after restriction,
  add isLoc int not null default 0 after notes,
  add key (modelType);

alter table InflectedForm
  add lexemId int not null default 0 after lexemModelId,
  add key (lexemId);

alter table LexemSource
  add lexemId int not null default 0 after lexemModelId,
  add key (lexemId);
