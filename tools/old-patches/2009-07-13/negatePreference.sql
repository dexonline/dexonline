-- remove the old COMMA_BELOW option
update User set Preferences = Preferences & ~1;
-- add the new CEDILLA_BELOW column
alter table User change Preferences Preferences set('CEDILLA_BELOW', 'FORCE_DIACRITICS', 'OLD_ORTHOGRAPHY');