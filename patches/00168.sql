alter table Entry
  add structStatus int not null default 1 after description,
  add structuristId int default null after structStatus,
  add key(structuristId);

update Entry
  set structuristId = 0;

update Lexem l
  join Entry e on l.entryId = e.id
  set e.structStatus = greatest(e.structStatus, l.structStatus),
      e.structuristId = greatest(ifnull(e.structuristId, 0), l.structuristId)
  where l.structStatus > 1;

update Entry
  set structuristId = null
  where structuristId = 0;

alter table Lexem
  drop structStatus,
  drop structuristId;
