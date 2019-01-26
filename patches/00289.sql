alter table Definition
  add suspiciousGlyphs varchar(255) after hasAmbiguousAbbreviations,
  add key (suspiciousGlyphs);
