create table Comment (
  Id int(11) not null auto_increment,
  DefId int(11) not null,
  UserId int(11) not null,
  Status int(11) not null,
  Contents text,
  HtmlContents text,
  primary key (Id),
  key(DefId)
)
