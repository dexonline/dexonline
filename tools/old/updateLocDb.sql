-- for LOC_5_0: alter table Lexem drop key `lexem_neaccentuat_2`;

alter table ConstraintMap
  add variant int not null default -1;

alter table InflectedForm
  change recommended recommended int default null;

alter table Lexem
  add charLength int after reverse,
  add number int after charLength,
  add entryId int after description,
  add consistentAccent int not null after noAccent,
  add verifSp int not null after frequency,
  add hyphenations varchar(255) after verifSp,
  add pronunciations varchar(255) after hyphenations,
  add variantOfId int after pronunciations,
  add structStatus int not null default 1 after variantOfId,
  add structuristId int after structStatus,
  add stopWord int not null after structuristId,
  add key `lexem_neaccentuat_2` (`formNoAccent`,`description`),
  add key `charLength` (`charLength`),
  add key `variantOf` (`variantOfId`),
  add key `comment` (`comment`(3)),
  add key `structuristId` (`structuristId`);

alter table LexemModel
  engine = MyISAM;

alter table LexemSource
  engine = MyISAM;

alter table Model
  change modelType modelType varchar(10) not null;

alter table ModelDescription
  change isLoc isLoc tinyint(1) not null,
  change recommended recommended tinyint(1) not null,
  add key `modelId` (`modelId`,`inflectionId`,`variant`);

alter table ModelType
  change code code varchar(10) not null,
  change canonical canonical varchar(10) not null;
