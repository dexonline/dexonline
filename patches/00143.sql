alter table Lexem
  add structuristId int after structStatus,
  add index(structuristId);
