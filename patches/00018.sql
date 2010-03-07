rename table constraints to ConstraintMap;
alter table ConstraintMap change constr_id code char(1) not null;
alter table ConstraintMap change infl_id inflectionId int not null;

rename table LOC_4_1.constraints to LOC_4_1.ConstraintMap;
alter table LOC_4_1.ConstraintMap change constr_id code char(1) not null;
alter table LOC_4_1.ConstraintMap change infl_id inflectionId int not null;

rename table LOC_4_0.constraints to LOC_4_0.ConstraintMap;
alter table LOC_4_0.ConstraintMap change constr_id code char(1) not null;
alter table LOC_4_0.ConstraintMap change infl_id inflectionId int not null;
