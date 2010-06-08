create table diverta_Book (
  id int not null auto_increment,
  sku varchar(50) not null,
  title varchar(255),
  author varchar(255),
  publisher varchar(255),
  imageUrl varchar(255),
  url varchar(255),
  thumbWidth int,
  thumbHeight int,
  impressions int,
  clicks int,
  createDate int not null,
  modDate int not null,
  primary key(id),
  unique key(sku)
);

create table diverta_Index (
  id int not null auto_increment,
  lexemId int not null,
  bookId int not null,
  primary key(id),
  key(lexemId),
  key(bookId)
);
