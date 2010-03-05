alter table Comment change Id id int not null auto_increment;
alter table Comment change DefinitionId definitionId int not null;
alter table Comment change UserId userId int not null;
alter table Comment change Status status int not null;
alter table Comment change Contents contents text;
alter table Comment change HtmlContents htmlContents text;
alter table Comment add createDate int not null after htmlContents;
alter table Comment add modDate int not null after createDate;
update Comment set createDate = unix_timestamp(), modDate = unix_timestamp();

alter table RecentLink change Id id int not null auto_increment;
alter table RecentLink change UserId userId int not null;
alter table RecentLink change VisitDate visitDate int not null;
alter table RecentLink change Url url varchar(255) not null;
alter table RecentLink change Text text varchar(255) not null;
