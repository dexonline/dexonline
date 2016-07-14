alter table Visual
  add entryId int not null after lexemeId;

alter table VisualTag
  add entryId int not null after lexemeId;

update Visual v
  join Lexem l on v.lexemeId = l.id
  set v.entryId = l.entryId;

update VisualTag v
  join Lexem l on v.lexemeId = l.id
  set v.entryId = l.entryId;

alter table Visual
  drop lexemeId;

alter table VisualTag
  drop lexemeId;
