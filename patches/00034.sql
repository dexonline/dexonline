alter table Lexem add column tags varchar(50) NOT NULL after description;
alter table Lexem add column source SET('doom2','dex98','dmlr','doom','dex09') after description;
