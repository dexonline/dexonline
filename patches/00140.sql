drop table if exists Autocomplete;

create table Autocomplete (
  formNoAccent char(50) collate utf8_romanian_ci not null,
  formUtf8General char(50) collate utf8_general_ci not null,
  key(formNoAccent),
  key(formUtf8General)
) engine=MyISAM charset=utf8;

insert into Autocomplete select distinct formNoAccent, formUtf8General from Lexem;
