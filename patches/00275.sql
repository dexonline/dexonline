alter table Lexeme
  add apheresis tinyint not null default 0;

alter table InflectedForm
  add apheresis tinyint not null default 0;

-- eu, tu, el, fi, sine, vrea
update Lexeme
  set apheresis = 1
  where id in (18468, 19519, 20836, 52656, 59076, 137123);

update Lexeme
  set apheresis = 1
  where formNoAccent like 'î%';
