alter table Synonym add type int not null after lexemId;
update Synonym set type = 1;
