alter table Inflection add animate int not null default 0;

update Inflection set animate = 1 where description like '%vocativ%';
