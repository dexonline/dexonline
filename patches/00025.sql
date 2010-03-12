alter table LexemDefinitionMap
  change Id id int not null auto_increment,
  change LexemId lexemId int not null,
  change DefinitionId definitionId int not null;
