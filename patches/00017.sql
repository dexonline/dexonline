rename table participle_models to ParticipleModel;
alter table ParticipleModel change pm_id id int not null auto_increment;
alter table ParticipleModel change pm_verb_model verbModel char(8) not null;
alter table ParticipleModel change pm_participle_model adjectiveModel char(8) not null;

rename table LOC_4_1.participle_models to LOC_4_1.ParticipleModel;
alter table LOC_4_1.ParticipleModel change pm_id id int not null auto_increment;
alter table LOC_4_1.ParticipleModel change pm_verb_model verbModel char(8) not null;
alter table LOC_4_1.ParticipleModel change pm_participle_model adjectiveModel char(8) not null;

rename table LOC_4_0.participle_models to LOC_4_0.ParticipleModel;
alter table LOC_4_0.ParticipleModel change pm_id id int not null auto_increment;
alter table LOC_4_0.ParticipleModel change pm_verb_model verbModel char(8) not null;
alter table LOC_4_0.ParticipleModel change pm_participle_model adjectiveModel char(8) not null;
