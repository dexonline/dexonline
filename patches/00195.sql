create table EntryLexem (
  id int not null auto_increment,
  entryId int not null,
  lexemId int not null,
  createDate int not null,
  modDate int not null,
  primary key (id),
  key (entryId),
  key (lexemId)
);

insert into EntryLexem (entryId, lexemId, createDate, modDate)
  select entryId, id, unix_timestamp(), unix_timestamp()
  from Lexem
  where entryId is not null;

alter table Lexem drop entryId;
