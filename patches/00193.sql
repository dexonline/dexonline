update Fragment set declension = 4 where declension > 4;

insert into ConstraintMap
  select 'N', id, -1
  from Inflection
  where description like '%genitiv%'
    or description like '%vocativ%';
