alter table MeaningTag
  add parentId int not null after id,
  add displayOrder int not null after parentId,
  add index(parentId),
  add index(displayOrder);

select @i := 0;
update MeaningTag set displayOrder = (select @i := @i + 1);
