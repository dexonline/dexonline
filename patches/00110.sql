alter table Lexem add index(comment(3));
update Lexem set comment = null where comment = '';
