alter table LOC_4_0.Lexem drop lexem_extra;
alter table LOC_4_0.Lexem add frequency float default 0 after noAccent;
alter table LOC_4_0.Model change modelType modelType varchar(10);
alter table LOC_4_0.ModelDescription add recommended int after isLoc;
alter table LOC_4_0.ModelType change code code varchar(10);
alter table LOC_4_0.ModelType change canonical canonical varchar(10);
alter table LOC_4_0.Source add link varchar(255) after year, add isActive tinyint(1) default 1 after year;
drop table LOC_4_0.dmlr_models;

alter table LOC_4_1.Lexem drop lexem_extra;
alter table LOC_4_1.Lexem add frequency float default 0 after noAccent;
alter table LOC_4_1.Model change modelType modelType varchar(10);
alter table LOC_4_1.ModelDescription add recommended int after isLoc;
alter table LOC_4_1.ModelType change code code varchar(10);
alter table LOC_4_1.ModelType change canonical canonical varchar(10);
alter table LOC_4_1.Source add link varchar(255) after year, add isActive tinyint(1) default 1 after year;
drop table LOC_4_1.dmlr_models;

alter table LOC_5_0.Model change modelType modelType varchar(10);
alter table LOC_5_0.ModelType change code code varchar(10);
alter table LOC_5_0.ModelType change canonical canonical varchar(10);
alter table LOC_5_0.Source add link varchar(255) after year, add isActive tinyint(1) default 1 after year;
