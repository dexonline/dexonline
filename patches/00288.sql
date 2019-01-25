alter table Source
  add commonGlyphs varchar(255) not null default '' after hasPageImages,
  add rareGlyphs varchar(255) not null default '' after commonGlyphs;
