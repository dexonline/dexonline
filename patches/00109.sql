 delete from LexemDefinitionMap where definitionId not in (select id from Definition);
