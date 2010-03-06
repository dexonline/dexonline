alter table FullTextIndex change LexemId lexemId int not null;
alter table FullTextIndex change InflectionId inflectionId int not null;
alter table FullTextIndex change DefinitionId definitionId int not null;
alter table FullTextIndex change Position position int not null;
