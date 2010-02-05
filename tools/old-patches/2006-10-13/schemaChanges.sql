-- This will end in an error if the database name is different, but that's ok
alter database DEX_dev charset utf8 collate utf8_romanian_ci;

alter table Word change DefinitionId ConceptId int not null;
alter table Word drop index `DefinitionId`;
alter table Word add index(ConceptId);
alter table Word drop index alfabetic;
alter table Word add index (Name);

drop table if exists Concept;
create table Concept (
  Id int not null auto_increment,
  Name varchar(40) not null,
  Description varchar(100) not null,
  unique key (Id),
  key (Name)
);

drop table if exists ConceptDefinitionMap;
create table ConceptDefinitionMap (
  Id int not null auto_increment,
  ConceptId int not null,
  DefinitionId int not null,
  unique key (Id),
  key (ConceptId),
  key (DefinitionId),
  key (ConceptId, DefinitionId)
);

drop table if exists RecentLink;
create table RecentLink (
  Id int not null auto_increment,
  UserId int not null,
  VisitDate int not null,
  Url varchar(255) not null,
  Text varchar(255) not null,
  unique key(Id),
  key (UserId)
);

alter table Comment charset utf8 collate utf8_romanian_ci;
alter table Comment change Contents Contents blob;
alter table Comment change Contents Contents text;
alter table Comment change HtmlContents HtmlContents blob;
alter table Comment change HtmlContents HtmlContents text;

alter table Declension charset utf8 collate utf8_romanian_ci;
alter table Declension change Name Name blob;
alter table Declension change Name Name varchar(255);

alter table DeclensionModel charset utf8 collate utf8_romanian_ci;
alter table DeclensionModel change BaseForm BaseForm blob;
alter table DeclensionModel change BaseForm BaseForm varchar(100);

alter table Definition charset utf8 collate utf8_romanian_ci;
alter table Definition drop index Lexicon;
alter table Definition change Lexicon Lexicon blob;
alter table Definition change Lexicon Lexicon varchar(100);
alter table Definition change InternalRep InternalRep blob;
alter table Definition change InternalRep InternalRep text;
alter table Definition change HtmlRep HtmlRep blob;
alter table Definition change HtmlRep HtmlRep text;
alter table Definition add index (Lexicon);

alter table GuideEntry charset utf8 collate utf8_romanian_ci;
alter table GuideEntry change Correct Correct blob;
alter table GuideEntry change Correct Correct text;
alter table GuideEntry change CorrectHtml CorrectHtml blob;
alter table GuideEntry change CorrectHtml CorrectHtml text;
alter table GuideEntry change Wrong Wrong blob;
alter table GuideEntry change Wrong Wrong text;
alter table GuideEntry change WrongHtml WrongHtml blob;
alter table GuideEntry change WrongHtml WrongHtml text;
alter table GuideEntry change Comments Comments blob;
alter table GuideEntry change Comments Comments text;
alter table GuideEntry change CommentsHtml CommentsHtml blob;
alter table GuideEntry change CommentsHtml CommentsHtml text;

alter table Inflection charset utf8 collate utf8_romanian_ci;
alter table Inflection change Name Name blob;
alter table Inflection change Name Name varchar(255);
alter table Inflection change Morphs Morphs blob;
alter table Inflection change Morphs Morphs varchar(255);

alter table Morph charset utf8 collate utf8_romanian_ci;
alter table Morph drop index Symbol;
alter table Morph change Symbol Symbol blob;
alter table Morph change Symbol Symbol varchar(20);
alter table Morph change Normal Normal blob;
alter table Morph change Normal Normal varchar(20);
alter table Morph change Applied Applied blob;
alter table Morph change Applied Applied varchar(20);
alter table Morph change Example Example blob;
alter table Morph change Example Example varchar(255);
alter table Morph add index (Symbol);

alter table PartOfSpeech charset utf8 collate utf8_romanian_ci;
alter table PartOfSpeech change Name Name blob;
alter table PartOfSpeech change Name Name varchar(255);
alter table PartOfSpeech change DisplayName DisplayName blob;
alter table PartOfSpeech change DisplayName DisplayName varchar(255);

alter table Source charset utf8 collate utf8_romanian_ci;
alter table Source change ShortName ShortName varchar(40) charset latin1;
alter table Source change ShortName ShortName blob;
alter table Source change ShortName ShortName varchar(40);
alter table Source change Name Name varchar(255) charset latin1;
alter table Source change Name Name blob;
alter table Source change Name Name varchar(255);
alter table Source change Author Author varchar(255) charset latin1;
alter table Source change Author Author blob;
alter table Source change Author Author varchar(255);
alter table Source change Publisher Publisher varchar(255) charset latin1;
alter table Source change Publisher Publisher blob;
alter table Source change Publisher Publisher varchar(255);
alter table Source change Year Year varchar(255) charset latin1;
alter table Source change Year Year blob;
alter table Source change Year Year varchar(255);

alter table SuffixApplication charset utf8 collate utf8_romanian_ci;
alter table SuffixApplication change Suffix Suffix blob;
alter table SuffixApplication change Suffix Suffix varchar(40);
alter table SuffixApplication change Remove Remove blob;
alter table SuffixApplication change Remove Remove varchar(40);
alter table SuffixApplication change Append Append blob;
alter table SuffixApplication change Append Append varchar(40);

alter table Typo charset utf8 collate utf8_romanian_ci;
alter table Typo change Problem Problem blob;
alter table Typo change Problem Problem varchar(400);

alter table User charset utf8 collate utf8_romanian_ci;
alter table User drop index Nick;
alter table User change Name Name blob;
alter table User change Name Name varchar(100);
alter table User change Nick Nick blob;
alter table User change Nick Nick varchar(100);
alter table User add index (Nick);

alter table WordForm charset utf8 collate utf8_romanian_ci;
alter table WordForm drop index Form;
alter table WordForm change Form Form blob;
alter table WordForm change Form Form varchar(100);
alter table WordForm add index (Form);

alter table Word charset utf8 collate utf8_romanian_ci;
alter table Word drop Name;
alter table Word drop index dname;
alter table Word change Dname Name blob;
alter table Word change Name Name varchar(100);
alter table Word add index (Name);

alter table changes charset utf8 collate utf8_romanian_ci;
alter table changes change diff diff blob;
alter table changes change diff diff text;
