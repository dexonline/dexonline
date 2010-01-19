-- add new 'EXCLUDE_UNOFFICIAL' option
alter table User change Preferences Preferences set('CEDILLA_BELOW', 'FORCE_DIACRITICS', 'OLD_ORTHOGRAPHY', 'EXCLUDE_UNOFFICIAL');
