alter table AccuracyProject
  drop method,
  drop step,
  drop lastCreateDate,
  drop ignoredDefinitions;

alter table AccuracyProject
  add defCount int not null after visibility,
  add errorRate double not null after defCount,
  add speed double not null after errorRate;

alter table AccuracyRecord
  add reviewed int not null after definitionId;

update AccuracyRecord
  set reviewed = 1;
