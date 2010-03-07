rename table transforms to Transform;
alter table Transform change transf_id id int not null auto_increment;
alter table Transform change transf_from transfFrom char(16) not null;
alter table Transform change transf_to transfTo char(16) not null;
alter table Transform drop transf_descr;

rename table LOC_4_1.transforms to LOC_4_1.Transform;
alter table LOC_4_1.Transform change transf_id id int not null auto_increment;
alter table LOC_4_1.Transform change transf_from transfFrom char(16) not null;
alter table LOC_4_1.Transform change transf_to transfTo char(16) not null;
alter table LOC_4_1.Transform drop transf_descr;

rename table LOC_4_0.transforms to LOC_4_0.Transform;
alter table LOC_4_0.Transform change transf_id id int not null auto_increment;
alter table LOC_4_0.Transform change transf_from transfFrom char(16) not null;
alter table LOC_4_0.Transform change transf_to transfTo char(16) not null;
alter table LOC_4_0.Transform drop transf_descr;
