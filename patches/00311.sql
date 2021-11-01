-- Migrate from MyISAM to InnoDB. Resources:
--   https://mariadb.com/kb/en/converting-tables-from-myisam-to-innodb/
--   https://dev.mysql.com/doc/refman/5.7/en/converting-tables-to-innodb.html

-- Make sure all tables have primary keys. Some are missing it, some have
-- unique keys with auto_increment instead.
alter table Abbreviation
  drop key `Id`,
  add primary key(id);

alter table AdsClick
  add id int not null auto_increment first,
  add primary key(id);

alter table Autocomplete
  add id int not null auto_increment first,
  add primary key(id);

alter table ConstraintMap
  add id int not null auto_increment first,
  add primary key(id);

alter table Definition
  drop key `Id`,
  add primary key(id);

alter table FullTextIndex
  add id int not null auto_increment first,
  add primary key(id);

alter table RecentLink
  drop key `Id`,
  add primary key(id);

alter table TraineeSource
  add id int not null auto_increment first,
  add primary key(id);

-- No reason to explicitly request indexes USING BTREE
alter table Abbreviation
  drop index `CreateDate`,
  add key(createDate),
  drop index `short`,
  add key(short),
  drop index `ModDate`,
  add key(modDate),
  drop index `sourceId`,
  add key(sourceId);

-- Drop redundant indexes INDEX(x, y, z, id) -> INDEX(x, y, z)
alter table EntryDefinition
  drop index `entryId_2`,
  add key(entryId, definitionRank),
  drop index `definitionId_2`,
  add key(definitionId, entryRank);

alter table EntryLexeme
  drop index `entryId_2`,
  add key(entryId, lexemeRank),
  drop index `lexemId_2`,
  add key(lexemeId, entryRank);

alter table LexemeSource
  drop index `lexemId`,
  add key(lexemeId, sourceRank),
  drop index `sourceId`,
  add key(sourceId, lexemeRank);

alter table MeaningSource
  drop index `meaningId_2`,
  add key(meaningId, sourceRank),
  drop index `sourceId`,
  add key(sourceId, meaningRank);

alter table TreeEntry
  drop index `treeId_2`,
  add key(treeId, entryRank),
  drop index `entryId_2`,
  add key(entryId, treeRank);

-- It's faster to regenrate FTI as converting it takes ~8 minutes
truncate table FullTextIndex;

-- Change engine from MyISAM to InnoDB
alter table Abbreviation        engine=InnoDB;
alter table AccuracyProject     engine=InnoDB;
alter table AccuracyRecord      engine=InnoDB;
alter table AdsClick            engine=InnoDB;
alter table AdsLink             engine=InnoDB;
alter table Autocomplete        engine=InnoDB;
alter table ConstraintMap       engine=InnoDB;
alter table Cookie              engine=InnoDB;
alter table CrawlerIgnoredUrl   engine=InnoDB;
alter table CrawlerUrl          engine=InnoDB;
alter table Definition          engine=InnoDB;
alter table DefinitionSimple    engine=InnoDB;
alter table DefinitionVersion   engine=InnoDB;
alter table Donation            engine=InnoDB;
alter table Entry               engine=InnoDB;
alter table EntryDefinition     engine=InnoDB;
alter table EntryLexeme         engine=InnoDB;
alter table Fragment            engine=InnoDB;
alter table FullTextIndex       engine=InnoDB;
alter table InflectedForm       engine=InnoDB;
alter table Inflection          engine=InnoDB;
alter table Lexeme              engine=InnoDB;
alter table LexemeSource        engine=InnoDB;
alter table Meaning             engine=InnoDB;
alter table MeaningSource       engine=InnoDB;
alter table Mention             engine=InnoDB;
alter table Model               engine=InnoDB;
alter table ModelDescription    engine=InnoDB;
alter table ModelType           engine=InnoDB;
alter table OCR                 engine=InnoDB;
alter table OCRLot              engine=InnoDB;
alter table ObjectTag           engine=InnoDB;
alter table OrthographicReforms engine=InnoDB;
alter table PageIndex           engine=InnoDB;
alter table ParticipleModel     engine=InnoDB;
alter table PasswordToken       engine=InnoDB;
alter table RandomWord          engine=InnoDB;
alter table RecentLink          engine=InnoDB;
alter table Relation            engine=InnoDB;
alter table SimilarSource       engine=InnoDB;
alter table Source              engine=InnoDB;
alter table SourceType          engine=InnoDB;
alter table Tag                 engine=InnoDB;
alter table Transform           engine=InnoDB;
alter table Tree                engine=InnoDB;
alter table TreeEntry           engine=InnoDB;
alter table Typo                engine=InnoDB;
alter table User                engine=InnoDB;
alter table UserWordBookmark    engine=InnoDB;
alter table Variable            engine=InnoDB;
alter table Visual              engine=InnoDB;
alter table VisualTag           engine=InnoDB;
alter table WikiArticle         engine=InnoDB;
alter table WikiKeyword         engine=InnoDB;
alter table WordOfTheDay        engine=InnoDB;
alter table WordOfTheDayHistory engine=InnoDB;
alter table WordOfTheMonth      engine=InnoDB;
alter table WotdArtist          engine=InnoDB;
