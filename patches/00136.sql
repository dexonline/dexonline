truncate table ConstraintMap;

insert into ConstraintMap select 'S', id from Inflection
  where description like '%plural%' and modelType = 'M';
insert into ConstraintMap select 'S', id from Inflection
  where description like '%plural%' and modelType = 'F';
insert into ConstraintMap select 'S', id from Inflection
  where description like '%plural%' and modelType = 'N';
insert into ConstraintMap select 'S', id from Inflection
  where description like '%plural%' and modelType = 'A';
insert into ConstraintMap select 'S', id from Inflection
  where description like '%plural%' and modelType = 'P';

insert into ConstraintMap select 'P', id from Inflection
  where description like '%singular%' and modelType = 'M';
insert into ConstraintMap select 'P', id from Inflection
  where description like '%singular%' and modelType = 'F';
insert into ConstraintMap select 'P', id from Inflection
  where description like '%singular%' and modelType = 'N';
insert into ConstraintMap select 'P', id from Inflection
  where description like '%singular%' and modelType = 'A';
insert into ConstraintMap select 'P', id from Inflection
  where description like '%singular%' and modelType = 'P';
-- for some reason the imperative plural is also forbidden here ('teleficați!')
insert into ConstraintMap select 'P', id from Inflection
  where (description like '%singular%' or description like '%imperativ%') and modelType = 'V';

insert into ConstraintMap select 'U', id from Inflection
  where (description like '%persoana I%' or description like '%persoana a II-a%') and modelType = 'V';

insert into ConstraintMap select 'I', id from Inflection
  where (description like '%persoana I%' or description like '%persoana a II-a%' or description like '%plural%') and modelType = 'V';

insert into ConstraintMap select 'T', id from Inflection
  where (description like '%indicativ, prezent%' or description like '%conjunctiv, prezent%' or description like '%imperativ%') and modelType = 'V';
