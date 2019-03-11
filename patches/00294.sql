alter table Source
  change canDistribute canDistribute int not null default 0,
  change structurable structurable int not null default 0,
  change dropdownOrder dropdownOrder int not null default 0,
  change ourDefCount ourDefCount int not null default 0;

alter table Lexeme
  change stopWord stopWord int not null default 0;

alter table Entry
  change adult adult int not null default 0;

alter table Tag
  change icon icon varchar(50) not null default '',
  change iconOnly iconOnly int not null default 0,
  change tooltip tooltip varchar(255) not null default '',
  change color color varchar(10) not null default '',
  change background background varchar(10) not null default '';
