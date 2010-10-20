-- Split inflection 84 ('invariabil') in two. We need this so that each inflection is only used in one model type. Currently, model types I and T share inflection 84.
insert into Inflection set description = 'Formă unică';
update ModelDescription set inflectionId = 85 where modelId = 2;
update Lexem, InflectedForm set InflectedForm.inflectionId = 85 where Lexem.id = lexemId and inflectionId = 84 and modelType = 'T';

-- Add a modelType column to Inflection (many-to-one).
alter table Inflection add modelType varchar(10), add rank int, add key(rank);
update Inflection set modelType = 'M', rank = id - 0 where id between 1 and 8;
update Inflection set modelType = 'F', rank = id - 8 where id between 9 and 16;
update Inflection set modelType = 'N', rank = id - 16 where id between 17 and 24;
update Inflection set modelType = 'A', rank = id - 24 where id between 25 and 40;
update Inflection set modelType = 'P', rank = id - 40 where id between 41 and 48;
update Inflection set modelType = 'V', rank = id - 48 where id between 49 and 83;
update Inflection set modelType = 'I', rank = id - 83 where id between 84 and 84;
update Inflection set modelType = 'T', rank = id - 84 where id between 85 and 85;

alter table ModelDescription add isLoc int after applOrder;
update ModelDescription set isLoc = 1 where applOrder = 0;
