alter table AccuracyProject
  add totalLength int not null after method,
  add timeSpent int not null after totalLength,
  add ignoredDefinitions int not null after timeSpent;
