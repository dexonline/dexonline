alter table VisualTag
  change textXCoord labelX int not null default 0,
  change textYCoord labelY int not null default 0,
  change imgXCoord tipX int not null default 0,
  change imgYCoord tipY int not null default 0;

update VisualTag
  set labelX = labelX + 5,
      labelY = labelY +  5,
      tipX = tipX +  5,
      tipY = tipY +  5;
