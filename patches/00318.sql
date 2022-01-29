alter table Abbreviation
  add html tinyint(1) not null default 0 after enforced;

update Abbreviation
  set html = 1
  where internalRep rlike '[$\'"]';
