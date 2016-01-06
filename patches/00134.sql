drop table if exists ForbiddenForm;
create table ForbiddenForm (
  id int not null auto_increment,
  lexemModelId int not null,
  inflectionId int not null,
  variant int not null,

  primary key (id),
  key (lexemModelId),
  key (lexemModelId, inflectionId, variant)
);
