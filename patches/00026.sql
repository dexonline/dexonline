rename table models to Model;
alter table Model
  change model_id id int not null auto_increment,
  change model_type modelType char(2) not null,
  change model_no number char(8) not null,
  change model_descr description text not null,
  change model_exponent exponent char(50) not null,
  change model_flag flag int not null;

rename table LOC_4_1.models to LOC_4_1.Model;
alter table LOC_4_1.Model
  change model_id id int not null auto_increment,
  change model_type modelType char(2) not null,
  change model_no number char(8) not null,
  change model_descr description text not null,
  change model_exponent exponent char(50) not null,
  change model_flag flag int not null;

rename table LOC_4_0.models to LOC_4_0.Model;
alter table LOC_4_0.Model
  change model_id id int not null auto_increment,
  change model_type modelType char(2) not null,
  change model_no number char(8) not null,
  change model_descr description text not null,
  change model_exponent exponent char(50) not null,
  change model_flag flag int not null;
