alter table Abbreviation
  change sourceId sourceId int(11) not null default 0,
  change `short` `short` varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0,
  change modUserId modUserId int(11) not null default 0;
alter table AccuracyProject
  change name name varchar(255) COLLATE utf8mb4_romanian_ci not null default '',
  change ownerId ownerId int(11) not null default 0,
  change userId userId int(11) not null default 0,
  change lexiconPrefix lexiconPrefix varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change visibility visibility int(11) not null default 0,
  change defCount defCount int(11) not null default 0,
  change speed speed double not null default 0,
  change totalLength totalLength int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table AccuracyRecord
  change projectId projectId int(11) not null default 0,
  change definitionId definitionId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table AdsClick
  change skey skey varchar(16) COLLATE utf8mb4_romanian_ci not null default '';
alter table AdsLink
  change skey skey varchar(16) COLLATE utf8mb4_romanian_ci not null default '',
  change name name varchar(32) COLLATE utf8mb4_romanian_ci not null default '',
  change url url varchar(255) COLLATE utf8mb4_romanian_ci not null default '';
alter table Autocomplete
  change formNoAccent formNoAccent varchar(50) COLLATE utf8mb4_romanian_ci not null default '',
  change formUtf8General formUtf8General varchar(50) CHARACTER SET utf8mb4 not null default '';
alter table ConstraintMap
  change code code char(1) COLLATE utf8mb4_romanian_ci not null default '',
  change inflectionId inflectionId int(11) not null default 0;
alter table Cookie
  change createDate createDate int(11) not null default 0;
alter table CrawlerIgnoredUrl
  change url url varchar(500) COLLATE utf8mb4_romanian_ci not null default '',
  change failureCount failureCount int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table CrawlerPhrase
  change crawlerUrlId crawlerUrlId int(11) not null default 0,
  change contents contents mediumtext COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table CrawlerUrl
  change url url varchar(500) COLLATE utf8mb4_romanian_ci not null default '',
  change author author varchar(500) COLLATE utf8mb4_romanian_ci not null default '',
  change title title varchar(500) COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table Definition
  change userId userId int(11) not null default 0,
  change sourceId sourceId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0,
  change modUserId modUserId int(11) not null default 0;
alter table DefinitionSimple
  change definition definition longtext COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table DefinitionVersion
  change definitionId definitionId int(11) not null default 0,
  change action action int(11) not null default 0,
  change sourceId sourceId int(11) not null default 0,
  change lexicon lexicon varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change status status int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modUserId modUserId int(11) not null default 0;
alter table Donation
  change email email varchar(255) COLLATE utf8mb4_romanian_ci not null default '',
  change amount amount int(11) not null default 0,
  change date date date not null default '0000-00-00',
  change userId userId int(11) not null default 0,
  change source source int(11) not null default 0,
  change emailSent emailSent int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table Entry
  change description description varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table EntryDefinition
  change entryId entryId int(11) not null default 0,
  change definitionId definitionId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table EntryLexeme
  change entryId entryId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table Fragment
  change partId partId int(11) not null default 0,
  change capitalized capitalized int(11) not null default 0,
  change declension declension int(11) not null default 0,
  change rank rank int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table FullTextIndex
  change inflectionId inflectionId int(11) not null default 0,
  change definitionId definitionId int(11) not null default 0,
  change position position int(11) not null default 0;
alter table HarmonizeModel
  change modelType modelType varchar(10) COLLATE utf8mb4_romanian_ci not null default '',
  change modelNumber modelNumber varchar(8) COLLATE utf8mb4_romanian_ci not null default '',
  change tagId tagId int(11) not null default 0,
  change newModelType newModelType varchar(10) COLLATE utf8mb4_romanian_ci not null default '',
  change newModelNumber newModelNumber varchar(8) COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table HarmonizeTag
  change modelType modelType varchar(10) COLLATE utf8mb4_romanian_ci not null default '',
  change modelNumber modelNumber varchar(8) COLLATE utf8mb4_romanian_ci not null default '',
  change tagId tagId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table InflectedForm
  change form form varchar(50) COLLATE utf8mb4_romanian_ci not null default '',
  change formNoAccent formNoAccent varchar(50) COLLATE utf8mb4_romanian_ci not null default '',
  change formUtf8General formUtf8General varchar(50) CHARACTER SET utf8mb4 not null default '',
  change inflectionId inflectionId int(11) not null default 0,
  change variant variant int(11) not null default 0;
alter table Inflection
  change description description varchar(255) COLLATE utf8mb4_romanian_ci not null default '';
alter table Lexeme
  change form form varchar(50) COLLATE utf8mb4_romanian_ci not null default '',
  change formNoAccent formNoAccent varchar(50) COLLATE utf8mb4_romanian_ci not null default '',
  change formUtf8General formUtf8General varchar(50) CHARACTER SET utf8mb4 not null default '',
  change reverse reverse varchar(50) COLLATE utf8mb4_romanian_ci not null default '',
  change description description varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change noAccent noAccent int(11) not null default 0,
  change consistentAccent consistentAccent int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table LexemeSource
  change sourceId sourceId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table Loc
  change version version varchar(10) COLLATE utf8mb4_romanian_ci not null default '',
  change form form varchar(20) COLLATE utf8mb4_romanian_ci not null default '';
alter table Meaning
  change parentId parentId int(11) not null default 0,
  change displayOrder displayOrder int(11) not null default 0,
  change breadcrumb breadcrumb varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change userId userId int(11) not null default 0,
  change treeId treeId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table MeaningSource
  change meaningId meaningId int(11) not null default 0,
  change sourceId sourceId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table Mention
  change meaningId meaningId int(11) not null default 0,
  change objectId objectId int(11) not null default 0,
  change objectType objectType int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table MillData
  change internalRep internalRep longtext COLLATE utf8mb4_romanian_ci NOT NULL default '';
alter table Model
  change modelType modelType varchar(10) COLLATE utf8mb4_romanian_ci not null default '',
  change number number varchar(8) COLLATE utf8mb4_romanian_ci not null default '',
  change description description mediumtext COLLATE utf8mb4_romanian_ci not null default '',
  change exponent exponent varchar(50) COLLATE utf8mb4_romanian_ci not null default '';
alter table ModelDescription
  change modelId modelId int(11) not null default 0,
  change inflectionId inflectionId int(11) not null default 0,
  change variant variant int(11) not null default 0,
  change applOrder applOrder int(11) not null default 0,
  change recommended recommended tinyint(1) not null default 0,
  change transformId transformId int(11) not null default 0,
  change accentShift accentShift int(11) not null default 0,
  change vowel vowel char(2) COLLATE utf8mb4_romanian_ci not null default '';
alter table ModelType
  change code code varchar(10) COLLATE utf8mb4_romanian_ci not null default '',
  change description description varchar(255) COLLATE utf8mb4_romanian_ci not null default '',
  change canonical canonical varchar(10) COLLATE utf8mb4_romanian_ci not null default '';
alter table OCR
  change sourceId sourceId int(11) not null default 0,
  change userId userId int(11) not null default 0;
alter table OCRLot
  change sourceId sourceId int(11) not null default 0,
  change userId userId int(11) not null default 0,
  change fileName fileName varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change fileSize fileSize int(11) not null default 0;
alter table ObjectTag
  change objectId objectId int(11) not null default 0,
  change objectType objectType int(11) not null default 0,
  change tagId tagId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table OrthographicReforms
  change name name varchar(40) COLLATE utf8mb4_romanian_ci not null default '';
alter table PageIndex
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0,
  change modUserId modUserId int(11) not null default 0;
alter table ParticipleModel
  change verbModel verbModel varchar(8) COLLATE utf8mb4_romanian_ci not null default '',
  change adjectiveModel adjectiveModel varchar(8) COLLATE utf8mb4_romanian_ci not null default '';
alter table PasswordToken
  change userId userId int(11) not null default 0,
  change token token varchar(50) COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0;
alter table RandomWord
  change cuv cuv varchar(50) COLLATE utf8mb4_romanian_ci not null default '',
  change surse surse varchar(255) COLLATE utf8mb4_romanian_ci not null default '';
alter table RecentLink
  change userId userId int(11) not null default 0,
  change visitDate visitDate int(11) not null default 0,
  change url url varchar(255) COLLATE utf8mb4_romanian_ci not null default '',
  change text text varchar(255) COLLATE utf8mb4_romanian_ci not null default '';
alter table Relation
  change meaningId meaningId int(11) not null default 0,
  change treeId treeId int(11) not null default 0,
  change type type int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table SimilarSource
  change sourceId sourceId int(11) not null default 0,
  change similarSource similarSource int(11) not null default 0;
alter table Source
  change type type int(11) not null default 0;
alter table SourceAuthor
  change sourceId sourceId int(11) not null default 0;
alter table SourceType
  change name name varchar(40) COLLATE utf8mb4_romanian_ci not null default '',
  change description description varchar(255) COLLATE utf8mb4_romanian_ci not null default '';
alter table Tag
  change parentId parentId int(11) not null default 0,
  change value value varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table TraineeSource
  change userId userId int(11) not null default 0;
alter table Transform
  change transfFrom transfFrom varchar(16) COLLATE utf8mb4_romanian_ci not null default '',
  change transfTo transfTo varchar(16) COLLATE utf8mb4_romanian_ci not null default '';
alter table Tree
  change description description varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change descriptionSort descriptionSort varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table TreeEntry
  change treeId treeId int(11) not null default 0,
  change entryId entryId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table Typo
  change definitionId definitionId int(11) not null default 0,
  change userName userName varchar(100) COLLATE utf8mb4_romanian_ci not null default '';
alter table UserWordBookmark
  change userId userId int(11) not null default 0,
  change definitionId definitionId int(11) not null default 0,
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table Variable
  change name name varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change value value varchar(100) COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table VisualTag
  change entryId entryId int(11) not null default 0;
alter table WikiArticle
  change pageId pageId int(11) not null default 0,
  change revId revId int(11) not null default 0,
  change title title varchar(150) COLLATE utf8mb4_romanian_ci not null default '',
  change fullUrl fullUrl varchar(255) COLLATE utf8mb4_romanian_ci not null default '',
  change wikiContents wikiContents longtext COLLATE utf8mb4_romanian_ci not null default '',
  change htmlContents htmlContents longtext COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table WikiKeyword
  change wikiArticleId wikiArticleId int(11) not null default 0,
  change keyword keyword varchar(100) COLLATE utf8mb4_romanian_ci not null default '';
alter table WordOfTheDay
  change userId userId int(11) not null default 0,
  change description description mediumtext COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table WordOfTheDayHistory
  change wotdId wotdId int(11) not null default 0,
  change userId userId int(11) not null default 0,
  change description description mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0,
  change modUserId modUserId int(11) not null default 0,
  change actionUserId actionUserId int(11) not null default 0;
alter table WordOfTheMonth
  change definitionId definitionId int(11) not null default 0,
  change description description varchar(255) COLLATE utf8mb4_romanian_ci not null default '',
  change createDate createDate int(11) not null default 0,
  change modDate modDate int(11) not null default 0;
alter table WotdArtist
  change label label varchar(50) COLLATE utf8mb4_romanian_ci not null default '',
  change name name varchar(255) COLLATE utf8mb4_romanian_ci not null default '';
alter table WotdAssignment
  change date date date not null default '0000-00-00',
  change artistId artistId int(11) not null default 0;
