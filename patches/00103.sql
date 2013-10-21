alter table CrawledPage add key(url);
alter table CrawledPage add createDate int not null, add modDate int not null;
alter table Link add createDate int not null, add modDate int not null;
