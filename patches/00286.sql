alter table DefinitionSimple
  add lexicon varchar(100) collate utf8_romanian_ci not null default '' after definition,
  add key(lexicon);

update DefinitionSimple ds
  join Definition d on ds.definitionId = d.id
  set ds.lexicon = d.lexicon;

alter table DefinitionSimple
  drop definitionId,
  drop pos;
