alter table Source add column IsOfficial tinyint(1) not null;
alter table Source add column DisplayOrder smallint not null;
update Source set IsOfficial = 1 where Id <> 22;
update Source set DisplayOrder = Id;

