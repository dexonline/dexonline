insert into ConstraintMap select 'S', id from Inflection where description like '%vocativ%singular%';
insert into ConstraintMap select 'P', id from Inflection where description like '%vocativ%plural%';
