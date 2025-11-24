alter table SourceType add column displayOrder int(11) not null after name;

update SourceType set displayOrder=id+1 where id < 20;

update SourceType set displayOrder=1 where id=12;
