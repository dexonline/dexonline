rename table model_description to ModelDescription;
alter table ModelDescription
  change md_id id int not null auto_increment,
  change md_model modelId int not null,
  change md_infl inflectionId int not null,
  change md_variant variant int not null,
  change md_order applOrder int not null,
  change md_transf transformId int not null,
  change md_accent_shift accentShift int not null,
  change md_vowel vowel char(2) not null;

rename table LOC_4_1.model_description to LOC_4_1.ModelDescription;
alter table LOC_4_1.ModelDescription
  change md_id id int not null auto_increment,
  change md_model modelId int not null,
  change md_infl inflectionId int not null,
  change md_variant variant int not null,
  change md_order applOrder int not null,
  change md_transf transformId int not null,
  change md_accent_shift accentShift int not null,
  change md_vowel vowel char(2) not null;

rename table LOC_4_0.model_description to LOC_4_0.ModelDescription;
alter table LOC_4_0.ModelDescription
  change md_id id int not null auto_increment,
  change md_model modelId int not null,
  change md_infl inflectionId int not null,
  change md_variant variant int not null,
  change md_order applOrder int not null,
  change md_transf transformId int not null,
  change md_accent_shift accentShift int not null,
  change md_vowel vowel char(2) not null;
