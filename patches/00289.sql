alter table Definition
  add suspiciousGlyphs varchar(255) after hasAmbiguousAbbreviations,
  add key (suspiciousGlyphs);

alter table Tag add public int not null default 1 after tooltip;
