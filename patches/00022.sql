alter table Typo change Id id int not null auto_increment,
  change DefinitionId definitionId int not null,
  change Problem problem varchar(400);
