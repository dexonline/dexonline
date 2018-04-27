alter table Lexeme
  change restriction restriction char(10) collate utf8_bin not null default '';
