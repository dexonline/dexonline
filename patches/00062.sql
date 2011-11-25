alter table Source add defCount int not null default -1, add ourDefCount int not null, add percentComplete decimal(7, 4) not null default -1;
