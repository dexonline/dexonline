-- permit the imperative plural for verbs with the 'P' restriction (see also 00136.sql)
delete from ConstraintMap where code = 'P' and inflectionId = '86' limit 1;
