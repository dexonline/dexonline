alter table Source add canDistribute int not null;
update Source set canDistribute = 0 where publisher like '%Litera%';
update Source set canDistribute = 1 where publisher not like '%Litera%';
