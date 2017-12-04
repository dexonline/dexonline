alter table EntryDefinition
  add entryRank int not null default 0 after definitionId,
  add definitionRank int not null default 0 after entryRank,
  add key(entryId, definitionRank, id),
  add key(definitionId, entryRank, id);

set @prevId = 0;
set @curRank = 0;
update EntryDefinition
  set entryRank = if(definitionId = @prevId,
                     @curRank := @curRank + 1,
                     @curRank := 1 + (@prevId := definitionId) * 0)
  order by definitionId, id;

set @prevId = 0;
set @curRank = 0;
update EntryDefinition
  set definitionRank = if(entryId = @prevId,
                       @curRank := @curRank + 1,
                       @curRank := 1 + (@prevId := entryId) * 0)
  order by entryId, id;


alter table EntryLexem
  add entryRank int not null default 0 after lexemId,
  add lexemRank int not null default 0 after entryRank,
  add key(entryId, lexemRank, id),
  add key(lexemId, entryRank, id);

set @prevId = 0;
set @curRank = 0;
update EntryLexem
  set entryRank = if(lexemId = @prevId,
                     @curRank := @curRank + 1,
                     @curRank := 1 + (@prevId := lexemId) * 0)
  order by lexemId, id;

set @prevId = 0;
set @curRank = 0;
update EntryLexem
  set lexemRank = if(entryId = @prevId,
                     @curRank := @curRank + 1,
                     @curRank := 1 + (@prevId := entryId) * 0)
  order by entryId, id;


alter table TreeEntry
  add treeRank int not null default 0 after entryId,
  add entryRank int not null default 0 after treeRank,
  add key(treeId, entryRank, id),
  add key(entryId, treeRank, id);

set @prevId = 0;
set @curRank = 0;
update TreeEntry
  set entryRank = if(treeId = @prevId,
                     @curRank := @curRank + 1,
                     @curRank := 1 + (@prevId := treeId) * 0)
  order by treeId, id;

set @prevId = 0;
set @curRank = 0;
update TreeEntry
  set treeRank = if(entryId = @prevId,
                    @curRank := @curRank + 1,
                    @curRank := 1 + (@prevId := entryId) * 0)
  order by entryId, id;


alter table LexemSource
  add lexemRank int not null default 0 after sourceId,
  add sourceRank int not null default 0 after lexemRank,
  add key(lexemId, sourceRank, id),
  add key(sourceId, lexemRank, id);

set @prevId = 0;
set @curRank = 0;
update LexemSource
  set sourceRank = if(lexemId = @prevId,
                      @curRank := @curRank + 1,
                      @curRank := 1 + (@prevId := lexemId) * 0)
  order by lexemId, id;

set @prevId = 0;
set @curRank = 0;
update LexemSource
  set lexemRank = if(sourceId = @prevId,
                     @curRank := @curRank + 1,
                     @curRank := 1 + (@prevId := sourceId) * 0)
  order by sourceId, id;


alter table MeaningSource
  add meaningRank int not null default 0 after sourceId,
  add sourceRank int not null default 0 after meaningRank,
  add key(meaningId, sourceRank, id),
  add key(sourceId, meaningRank, id);

set @prevId = 0;
set @curRank = 0;
update MeaningSource
  set sourceRank = if(meaningId = @prevId,
                      @curRank := @curRank + 1,
                      @curRank := 1 + (@prevId := meaningId) * 0)
  order by meaningId, id;

set @prevId = 0;
set @curRank = 0;
update MeaningSource
  set meaningRank = if(sourceId = @prevId,
                       @curRank := @curRank + 1,
                       @curRank := 1 + (@prevId := sourceId) * 0)
  order by sourceId, id;
