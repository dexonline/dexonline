delete from InflectedForm where lexemeId in (select id from Lexeme where apheresis);
delete from EntryLexeme where lexemeId in (select id from Lexeme where apheresis);
delete from LexemeSource where lexemeId in (select id from Lexeme where apheresis);
delete from Fragment where lexemeId in (select id from Lexeme where apheresis);
delete from Fragment where partId in (select id from Lexeme where apheresis);

delete from ObjectTag
  where objectType = 2
  and objectId in (select id from Lexeme where apheresis);

delete from Lexeme where apheresis;
alter table Lexeme drop apheresis;
