create table Definition (
  Id		int not null auto_increment,
  UserId	int not null,
  SourceId	int not null,
  Lexicon 	varchar(30) character set latin1 collate latin1_bin not null,
  Displayed     int not null,
  InternalRep	text,
  HtmlRep	text,
  Status	int not null default 0,
  CreateDate	int not null default 0,
  ModDate	int not null default 0,
  unique key (Id),
  key (UserId),
  key (Lexicon),
  key (CreateDate),
  key (ModDate),
  key (Status)
);

alter table Word change Counter Id int not null auto_increment;
alter table Word change Name Name varchar(30) not null;
alter table Word change Dname Dname varchar(40) not null;
alter table Word add Priority int not null;
alter table Word add DefinitionId int not null;
alter table Word add index (DefinitionId);

-- The actual migration happens here;
update Word set DefinitionId = Id, Priority = 0;
insert into Definition
  (Id, UserId, SourceId, Lexicon, Displayed, InternalRep, HtmlRep, Status,
   CreateDate, ModDate)
  select Id, UserId, SourceId, Lexicon, Displayed, Def, HtmlDef, Status,
  CreateDate, ModDate from Word;

alter table Word drop Def;
alter table Word drop HtmlDef;
alter table Word drop Lexicon;
alter table Word drop UserId;
alter table Word drop SourceId;
alter table Word drop Displayed;
alter table Word drop CreateDate;
alter table Word drop ModDate;
alter table Word drop Status;

alter table Typo change WordId DefinitionId int not null;
alter table Comment change DefId DefinitionId int not null;
