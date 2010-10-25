alter table ModelDescription add recommended int after isLoc;
update ModelDescription set recommended = 1;
update ModelDescription set recommended = 0 where inflectionId in (81,82) and variant = 1 and applOrder = 0;

alter table InflectedForm add recommended int;
update InflectedForm set recommended = 1;
update InflectedForm set recommended = 0 where inflectionId in (81, 82) and variant = 1;
