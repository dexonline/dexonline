alter table models
  add model_exponent char(50) not null,
  add model_flag int not null;

update models, model_exponents
  set model_exponent = model_exponents.form,
  model_flag = model_exponents.flag
  where binary models.model_type = model_exponents.model_type
  and binary models.model_no = model_exponents.model_no;

drop table model_exponents;
