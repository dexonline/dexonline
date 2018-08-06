alter table TraineeSource
  change idUser userId int not null,
  change idSource sourceId int not null;
