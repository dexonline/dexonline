alter table Definition add index(abbrevReview);
alter table Lexem add consistentAccent int not null after noAccent;
update Lexem set consistentAccent = (form like "%'%") xor noAccent;
alter table Lexem add index(modelType);
