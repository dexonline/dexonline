insert into ConstraintMap
  select 'V', id, 1
  from Inflection
  where description rlike '(masculin|neutru).*vocativ.*singular';

insert into ConstraintMap
  select 'v', id, 0
  from Inflection
  where description rlike '(masculin|neutru).*vocativ.*singular';

insert into ConstraintMap
  select 'W', id, 1
  from Inflection
  where description rlike 'feminin.*vocativ.*singular';

insert into ConstraintMap
  select 'w', id, 0
  from Inflection
  where description rlike 'feminin.*vocativ.*singular';
