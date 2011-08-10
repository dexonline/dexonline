alter table LOC_4_0.ModelDescription add isLoc int after applOrder;
update LOC_4_0.ModelDescription set isLoc = 1 where applOrder = 0;

alter table LOC_4_1.ModelDescription add isLoc int after applOrder;
update LOC_4_1.ModelDescription set isLoc = 1 where applOrder = 0;
