alter table User
  change preferences textPreferences set('CEDILLA_BELOW','FORCE_DIACRITICS','OLD_ORTHOGRAPHY','EXCLUDE_UNOFFICIAL','SHOW_PARADIGM','LOC_PARADIGM','SHOW_ADVANCED','PRIVATE_MODE');

alter table User
  add preferences int not null after moderator;
