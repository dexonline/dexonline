rename table model_types to ModelType;
alter table ModelType change mt_id id int not null auto_increment,
  change mt_value code char(2) not null,
  change mt_descr description varchar(255) not null,
  change mt_canonical canonical char(2) not null;

rename table LOC_4_1.model_types to LOC_4_1.ModelType;
alter table LOC_4_1.ModelType change mt_id id int not null auto_increment,
  change mt_value code char(2) not null,
  change mt_descr description varchar(255) not null,
  change mt_canonical canonical char(2) not null;

rename table LOC_4_0.model_types to LOC_4_0.ModelType;
alter table LOC_4_0.ModelType change mt_id id int not null auto_increment,
  change mt_value code char(2) not null,
  change mt_descr description varchar(255) not null,
  change mt_canonical canonical char(2) not null;
