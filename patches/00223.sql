alter table AccuracyProject
  add step int not null default 1 after method,
  add lastCreateDate int not null after step;
