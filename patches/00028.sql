CREATE TABLE AdsLink (skey varchar(16) NOT NULL, name varchar(32) NOT NULL, url varchar(255) NOT NULL, PRIMARY KEY skey (skey));
INSERT INTO AdsLink VALUES ('elvsoft', 'Elvsoft', 'http://www.elvsoft.ro'), ('litera', 'Editura Litera', 'http://www.litera.ro'), ('siveco', 'Siveco', 'http://siveco.ro'), ('saeculum', 'Editura Saeculum', 'http://saeculum.ro');
CREATE TABLE AdsClick(skey varchar(16) NOT NULL, ip int unsigned, ts timestamp default now(), primary key needed (skey, ip, ts));
