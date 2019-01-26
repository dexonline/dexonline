alter table Definition
  add rareGlyphs varchar(150) not null default '' after hasAmbiguousAbbreviations,
  add key (rareGlyphs);

alter table Tag add public int not null default 1 after tooltip;

alter table Source drop rareGlyphs;
