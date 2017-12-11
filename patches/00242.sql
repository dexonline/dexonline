alter table EntryLexem
  add main int not null default 1 after lexemRank,
  add key(entryId, main);

update EntryLexem el
  join Lexem l on el.lexemId = l.id
  set el.main = l.main;

alter table Lexem drop main;
