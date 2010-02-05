-- add new 'SHOW_PARADIGM' option
alter table User change Preferences Preferences set('CEDILLA_BELOW', 'FORCE_DIACRITICS', 'OLD_ORTHOGRAPHY', 'EXCLUDE_UNOFFICIAL','SHOW_PARADIGM');
