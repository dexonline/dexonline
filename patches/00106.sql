alter table Lexem change structSealed structStatus int not null default 1;
update Lexem set structStatus = 1;
