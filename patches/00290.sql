alter table AccuracyProject
  change errorRate errorRate double not null default 0.0,
  change sourceId sourceId int not null default 0,
  change startDate startDate date not null default '0000-00-00',
  change endDate endDate date not null default '0000-00-00';

alter table AccuracyRecord
  change reviewed reviewed int not null default 0,
  change errors errors int not null default 0;
