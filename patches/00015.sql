rename table inflections to Inflection;
alter table Inflection change infl_id id int not null auto_increment;
alter table Inflection change infl_descr description varchar(255) not null;

rename table LOC_4_1.inflections to LOC_4_1.Inflection;
alter table LOC_4_1.Inflection change infl_id id int not null auto_increment;
alter table LOC_4_1.Inflection change infl_descr description varchar(255) not null;

rename table LOC_4_0.inflections to LOC_4_0.Inflection;
alter table LOC_4_0.Inflection change infl_id id int not null auto_increment;
alter table LOC_4_0.Inflection change infl_descr description varchar(255) not null;
