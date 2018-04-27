alter table Tree
  change description descriptionSort varchar(255) collate utf8_romanian_ci not null,
  add description varchar(255) collate utf8_bin not null after id;

update Tree set description = descriptionSort;
