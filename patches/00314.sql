truncate table FullTextIndex;
alter table FullTextIndex
  drop primary key;
alter table FullTextIndex
  drop column inflectionId;
alter table FullTextIndex
  add primary key (lexemeId, definitionId, position);
