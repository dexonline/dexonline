update ModelDescription set isLoc = 0 where isLoc is null;
update ModelDescription set recommended = 0 where recommended is null;
alter table ModelDescription modify isLoc bool not null;
alter table ModelDescription modify recommended bool not null;

-- Speed up the propagation from MD.recommended to InflectedForm.recommended
alter table ModelDescription add index (modelId, inflectionId, variant);
