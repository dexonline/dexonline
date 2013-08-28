update ModelType set description = lower(description);
alter table ModelType change code code varchar(10) not null, change canonical canonical varchar(10) not null;
alter table Lexem change modelType modelType varchar(10) not null;
alter table Model change modelType modelType varchar(10) not null;
