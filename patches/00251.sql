alter table AccuracyProject
  drop method,
  drop step,
  drop lastCreateDate;

alter table AccuracyRecord
  add reviewed int not null after definitionId;

update AccuracyRecord
  set reviewed = 1;
