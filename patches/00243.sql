rename table EntryLexem to EntryLexeme;
rename table Lexem to Lexeme;
rename table LexemSource to LexemeSource;

alter table EntryLexeme
  change lexemId lexemeId int not null default 0,
  change lexemRank lexemeRank int not null default 0;

alter table Fragment
  change lexemId lexemeId int not null default 0;

alter table FullTextIndex
  change lexemId lexemeId int not null default 0;

alter table InflectedForm
  change lexemId lexemeId int not null default 0;

alter table LexemeSource
  change lexemId lexemeId int not null default 0,
  change lexemRank lexemeRank int not null default 0;

alter table NGram
  change lexemId lexemeId int not null default 0;

alter table diverta_Index
  change lexemId lexemeId int not null default 0;
