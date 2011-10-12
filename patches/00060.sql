alter table LexemDefinitionMap add column createDate int not null, add column modDate int not null;
update LexemDefinitionMap set createDate = unix_timestamp(), modDate = unix_timestamp();
