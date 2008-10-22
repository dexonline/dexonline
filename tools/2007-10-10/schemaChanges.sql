create table FullTextIndex (
  LexemId int not null,
  InflectionId int not null,
  DefinitionId int not null,
  Position int not null,
  index(LexemId),
  index(DefinitionId)
);
