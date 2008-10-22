alter database character set utf8 collate utf8_romanian_ci;
drop table if exists constraints;
create table constraints (
  constr_id char(1) not null,
  infl_id int not null
);

drop table if exists dmlr_models;
create table dmlr_models (
  model_type char(2) not null,
  model_no char(10) not null,
  form char(50) not null,
  infl_id int not null,
  variant int not null,
  is_baseform int not null,
  key model (model_type, model_no),
  key infl_id (infl_id)
);

drop table if exists inflections;
create table inflections (
  infl_id int not null auto_increment,
  infl_descr varchar(255) not null,
  primary key (infl_id)
);

drop table if exists lexems;
create table lexems (
  lexem_id int not null auto_increment,
  lexem_forma char(50) not null,
  lexem_neaccentuat char(50) not null,
  lexem_utf8_general char(50) not null collate utf8_general_ci,
  lexem_invers char(50) not null,
  lexem_descr varchar(255) not null,
  lexem_model_type char(2) not null,
  lexem_model_no char(8) not null,
  lexem_restriction char(4) not null,
  lexem_extra char(50) not null,
  lexem_parse_info char(50) not null,
  lexem_comment varchar(255) not null,
  lexem_is_loc int not null,
  primary key (lexem_id),
  key lexem_model (lexem_model_type, lexem_model_no),
  key (lexem_forma),
  key (lexem_neaccentuat),
  key (lexem_utf8_general),
  key (lexem_invers)
);

drop table if exists model_description;
create table model_description (
  md_id int not null auto_increment,
  md_model int not null,
  md_infl int not null,
  md_variant int not null,
  md_order int not null,
  md_transf int not null,
  md_accent_shift int not null,
  md_vowel char(2) not null,
  primary key (md_id),
  key (md_model),
  key (md_transf)
);

drop table if exists model_exponents;
create table model_exponents (
  model_type char(2) not null,
  model_no char(8) not null,
  form char(50) not null,
  flag int not null,
  key lexem_model (model_type,model_no)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

drop table if exists model_mappings;
create table model_mappings (
  model_type char(2) not null,
  slave_no char(8) not null,
  master_no char(8) not null,
  unique key (model_type, slave_no)
);

drop table if exists model_types;
create table model_types (
  mt_id int not null auto_increment,
  mt_value char(2) not null,
  mt_descr varchar(255) not null,
  mt_canonical char(2) not null,
  primary key (mt_id),
  key (mt_value),
  key (mt_canonical)
);

drop table if exists models;
create table models (
  model_id int not null auto_increment,
  model_type char(2) not null,
  model_no char(8) not null,
  model_descr text not null,
  primary key (model_id),
  key morf (model_type,model_no)
);

-- This table maps verb models to their respective participle models. In
-- general, the participle model is A2, but not always.
drop table if exists participle_models;
create table participle_models (
  pm_id int not null auto_increment,
  pm_verb_model char(8) not null,
  pm_participle_model char(8) not null,
  primary key (pm_id),
  unique key (pm_verb_model)
);

drop table if exists transforms;
create table transforms (
  transf_id int not null auto_increment,
  transf_from char(16) not null,
  transf_to char(16) not null,
  transf_descr varchar(255) not null,
  primary key (transf_id)
);

drop table if exists wordlist;
create table wordlist (
  wl_form char(50) not null,
  wl_neaccentuat char(50) not null,
  wl_utf8_general char(50) not null collate utf8_general_ci,
  wl_lexem int not null,
  wl_analyse int not null,
  wl_variant int not null,
  key (wl_form),
  key (wl_neaccentuat),
  key (wl_utf8_general),
  key (wl_lexem),
  key (wl_analyse)
);

-- This table maps lexems to definitions. It replaces ConceptDefinitionMap.
drop table if exists LexemDefinitionMap;
create table LexemDefinitionMap (
  Id int not null auto_increment,
  LexemId int not null,
  DefinitionId int not null,
  unique key (Id),
  key (LexemId),
  key (DefinitionId)
);

insert into model_types set mt_value = 'I',
  mt_descr = 'Invariabil',
  mt_canonical = 'I';
insert into model_types set mt_value = 'M',
  mt_descr = 'Substantiv masculin',
  mt_canonical = 'M';
insert into model_types set mt_value = 'F',
  mt_descr = 'Substantiv feminin',
  mt_canonical = 'F';
insert into model_types set mt_value = 'N',
  mt_descr = 'Substantiv neutru',
  mt_canonical = 'N';
insert into model_types set mt_value = 'A',
  mt_descr = 'Adjectiv',
  mt_canonical = 'A';
insert into model_types set mt_value = 'MF',
  mt_descr = 'Substantiv masculin şi feminin',
  mt_canonical = 'A';
insert into model_types set mt_value = 'P',
  mt_descr = 'Pronume',
  mt_canonical = 'P';
insert into model_types set mt_value = 'V',
  mt_descr = 'Verb',
  mt_canonical = 'V';
insert into model_types set mt_value = 'VT',
  mt_descr = 'Verb tranzitiv',
  mt_canonical = 'V';
insert into model_types set mt_value = 'T',
  mt_descr = 'Temporar',
  mt_canonical = 'T';

insert into inflections set infl_id = 1, infl_descr =
  'Substantiv masculin, Nominativ-Acuzativ, singular, nearticulat';
insert into inflections set infl_id = 2, infl_descr =
  'Substantiv masculin, Genitiv-Dativ, singular, nearticulat';
insert into inflections set infl_id = 3, infl_descr =
  'Substantiv masculin, Nominativ-Acuzativ, plural, nearticulat';
insert into inflections set infl_id = 4, infl_descr =
  'Substantiv masculin, Genitiv-Dativ, plural, nearticulat';
insert into inflections set infl_id = 5, infl_descr =
  'Substantiv masculin, Nominativ-Acuzativ, singular, articulat';
insert into inflections set infl_id = 6, infl_descr =
  'Substantiv masculin, Genitiv-Dativ, singular, articulat';
insert into inflections set infl_id = 7, infl_descr =
  'Substantiv masculin, Nominativ-Acuzativ, plural, articulat';
insert into inflections set infl_id = 8, infl_descr =
  'Substantiv masculin, Genitiv-Dativ, plural, articulat';
insert into inflections set infl_id = 9, infl_descr =
  'Substantiv feminin, Nominativ-Acuzativ, singular, nearticulat';
insert into inflections set infl_id = 10, infl_descr =
  'Substantiv feminin, Genitiv-Dativ, singular, nearticulat';
insert into inflections set infl_id = 11, infl_descr =
  'Substantiv feminin, Nominativ-Acuzativ, plural, nearticulat';
insert into inflections set infl_id = 12, infl_descr =
  'Substantiv feminin, Genitiv-Dativ, plural, nearticulat';
insert into inflections set infl_id = 13, infl_descr =
  'Substantiv feminin, Nominativ-Acuzativ, singular, articulat';
insert into inflections set infl_id = 14, infl_descr =
  'Substantiv feminin, Genitiv-Dativ, singular, articulat';
insert into inflections set infl_id = 15, infl_descr =
  'Substantiv feminin, Nominativ-Acuzativ, plural, articulat';
insert into inflections set infl_id = 16, infl_descr =
  'Substantiv feminin, Genitiv-Dativ, plural, articulat';
insert into inflections set infl_id = 17, infl_descr =
  'Substantiv neutru, Nominativ-Acuzativ, singular, nearticulat';
insert into inflections set infl_id = 18, infl_descr =
  'Substantiv neutru, Genitiv-Dativ, singular, nearticulat';
insert into inflections set infl_id = 19, infl_descr =
  'Substantiv neutru, Nominativ-Acuzativ, plural, nearticulat';
insert into inflections set infl_id = 20, infl_descr =
  'Substantiv neutru, Genitiv-Dativ, plural, nearticulat';
insert into inflections set infl_id = 21, infl_descr =
  'Substantiv neutru, Nominativ-Acuzativ, singular, articulat';
insert into inflections set infl_id = 22, infl_descr =
  'Substantiv neutru, Genitiv-Dativ, singular, articulat';
insert into inflections set infl_id = 23, infl_descr =
  'Substantiv neutru, Nominativ-Acuzativ, plural, articulat';
insert into inflections set infl_id = 24, infl_descr =
  'Substantiv neutru, Genitiv-Dativ, plural, articulat';
insert into inflections set infl_id = 25, infl_descr =
  'Adjectiv, masculin, Nominativ-Acuzativ, singular, nearticulat';
insert into inflections set infl_id = 26, infl_descr =
  'Adjectiv, masculin, Genitiv-Dativ, singular, nearticulat';
insert into inflections set infl_id = 27, infl_descr =
  'Adjectiv, masculin, Nominativ-Acuzativ, plural, nearticulat';
insert into inflections set infl_id = 28, infl_descr =
  'Adjectiv, masculin, Genitiv-Dativ, plural, nearticulat';
insert into inflections set infl_id = 29, infl_descr =
  'Adjectiv, masculin, Nominativ-Acuzativ, singular, articulat';
insert into inflections set infl_id = 30, infl_descr =
  'Adjectiv, masculin, Genitiv-Dativ, singular, articulat';
insert into inflections set infl_id = 31, infl_descr =
  'Adjectiv, masculin, Nominativ-Acuzativ, plural, articulat';
insert into inflections set infl_id = 32, infl_descr =
  'Adjectiv, masculin, Genitiv-Dativ, plural, articulat';
insert into inflections set infl_id = 33, infl_descr =
  'Adjectiv, feminin, Nominativ-Acuzativ, singular, nearticulat';
insert into inflections set infl_id = 34, infl_descr =
  'Adjectiv, feminin, Genitiv-Dativ, singular, nearticulat';
insert into inflections set infl_id = 35, infl_descr =
  'Adjectiv, feminin, Nominativ-Acuzativ, plural, nearticulat';
insert into inflections set infl_id = 36, infl_descr =
  'Adjectiv, feminin, Genitiv-Dativ, plural, nearticulat';
insert into inflections set infl_id = 37, infl_descr =
  'Adjectiv, feminin, Nominativ-Acuzativ, singular, articulat';
insert into inflections set infl_id = 38, infl_descr =
  'Adjectiv, feminin, Genitiv-Dativ, singular, articulat';
insert into inflections set infl_id = 39, infl_descr =
  'Adjectiv, feminin, Nominativ-Acuzativ, plural, articulat';
insert into inflections set infl_id = 40, infl_descr =
  'Adjectiv, feminin, Genitiv-Dativ, plural, articulat';
insert into inflections set infl_id = 41, infl_descr =
  'Pronume, Nominativ-Acuzativ, singular, masculin';
insert into inflections set infl_id = 42, infl_descr =
  'Pronume, Genitiv-Dativ, singular, masculin';
insert into inflections set infl_id = 43, infl_descr =
  'Pronume, Nominativ-Acuzativ, plural, masculin';
insert into inflections set infl_id = 44, infl_descr =
  'Pronume, Genitiv-Dativ, plural, masculin';
insert into inflections set infl_id = 45, infl_descr =
  'Pronume, Nominativ-Acuzativ, singular, feminin';
insert into inflections set infl_id = 46, infl_descr =
  'Pronume, Genitiv-Dativ, singular, feminin';
insert into inflections set infl_id = 47, infl_descr =
  'Pronume, Nominativ-Acuzativ, plural, feminin';
insert into inflections set infl_id = 48, infl_descr =
  'Pronume, Genitiv-Dativ, plural, feminin';
insert into inflections set infl_id = 49, infl_descr =
  'Verb, Infinitiv prezent';
insert into inflections set infl_id = 50, infl_descr =
  'Verb, Infinitiv lung';
insert into inflections set infl_id = 51, infl_descr =
  'Verb, Imperativ, persoana a II-a, singular';
insert into inflections set infl_id = 52, infl_descr =
  'Verb, Participiu pasiv';
insert into inflections set infl_id = 53, infl_descr =
  'Verb, Gerunziu';

insert into inflections set infl_id = 54, infl_descr =
  'Verb, Indicativ, prezent, persoana I, singular';
insert into inflections set infl_id = 55, infl_descr =
  'Verb, Indicativ, prezent, persoana a II-a, singular';
insert into inflections set infl_id = 56, infl_descr =
  'Verb, Indicativ, prezent, persoana a III-a, singular';
insert into inflections set infl_id = 57, infl_descr =
  'Verb, Indicativ, prezent, persoana I, plural';
insert into inflections set infl_id = 58, infl_descr =
  'Verb, Indicativ, prezent, persoana a II-a, plural';
insert into inflections set infl_id = 59, infl_descr =
  'Verb, Indicativ, prezent, persoana a III-a, plural';

insert into inflections set infl_id = 60, infl_descr =
  'Verb, Conjunctiv, prezent, persoana I, singular';
insert into inflections set infl_id = 61, infl_descr =
  'Verb, Conjunctiv, prezent, persoana a II-a, singular';
insert into inflections set infl_id = 62, infl_descr =
  'Verb, Conjunctiv, prezent, persoana a III-a, singular';
insert into inflections set infl_id = 63, infl_descr =
  'Verb, Conjunctiv, prezent, persoana I, plural';
insert into inflections set infl_id = 64, infl_descr =
  'Verb, Conjunctiv, prezent, persoana a II-a, plural';
insert into inflections set infl_id = 65, infl_descr =
  'Verb, Conjunctiv, prezent, persoana a III-a, plural';

insert into inflections set infl_id = 66, infl_descr =
  'Verb, Indicativ, imperfect, persoana I, singular';
insert into inflections set infl_id = 67, infl_descr =
  'Verb, Indicativ, imperfect, persoana a II-a, singular';
insert into inflections set infl_id = 68, infl_descr =
  'Verb, Indicativ, imperfect, persoana a III-a, singular';
insert into inflections set infl_id = 69, infl_descr =
  'Verb, Indicativ, imperfect, persoana I, plural';
insert into inflections set infl_id = 70, infl_descr =
  'Verb, Indicativ, imperfect, persoana a II-a, plural';
insert into inflections set infl_id = 71, infl_descr =
  'Verb, Indicativ, imperfect, persoana a III-a, plural';

insert into inflections set infl_id = 72, infl_descr =
  'Verb, Indicativ, perfect simplu, persoana I, singular';
insert into inflections set infl_id = 73, infl_descr =
  'Verb, Indicativ, perfect simplu, persoana a II-a, singular';
insert into inflections set infl_id = 74, infl_descr =
  'Verb, Indicativ, perfect simplu, persoana a III-a, singular';
insert into inflections set infl_id = 75, infl_descr =
  'Verb, Indicativ, perfect simplu, persoana I, plural';
insert into inflections set infl_id = 76, infl_descr =
  'Verb, Indicativ, perfect simplu, persoana a II-a, plural';
insert into inflections set infl_id = 77, infl_descr =
  'Verb, Indicativ, perfect simplu, persoana a III-a, plural';

insert into inflections set infl_id = 78, infl_descr =
  'Verb, Indicativ, mai mult ca perfect, persoana I, singular';
insert into inflections set infl_id = 79, infl_descr =
  'Verb, Indicativ, mai mult ca perfect, persoana a II-a, singular';
insert into inflections set infl_id = 80, infl_descr =
  'Verb, Indicativ, mai mult ca perfect, persoana a III-a, singular';
insert into inflections set infl_id = 81, infl_descr =
  'Verb, Indicativ, mai mult ca perfect, persoana I, plural';
insert into inflections set infl_id = 82, infl_descr =
  'Verb, Indicativ, mai mult ca perfect, persoana a II-a, plural';
insert into inflections set infl_id = 83, infl_descr =
  'Verb, Indicativ, mai mult ca perfect, persoana a III-a, plural';

insert into inflections set infl_id = 84, infl_descr =
  'Invariabil';

-- Singular (nouns)
insert into constraints set constr_id = 'S', infl_id = 1;
insert into constraints set constr_id = 'S', infl_id = 2;
insert into constraints set constr_id = 'S', infl_id = 5;
insert into constraints set constr_id = 'S', infl_id = 6;
insert into constraints set constr_id = 'S', infl_id = 9;
insert into constraints set constr_id = 'S', infl_id = 10;
insert into constraints set constr_id = 'S', infl_id = 13;
insert into constraints set constr_id = 'S', infl_id = 14;
insert into constraints set constr_id = 'S', infl_id = 17;
insert into constraints set constr_id = 'S', infl_id = 18;
insert into constraints set constr_id = 'S', infl_id = 21;
insert into constraints set constr_id = 'S', infl_id = 22;

-- Singular (adjectives)
insert into constraints set constr_id = 'S', infl_id = 25;
insert into constraints set constr_id = 'S', infl_id = 26;
insert into constraints set constr_id = 'S', infl_id = 29;
insert into constraints set constr_id = 'S', infl_id = 30;
insert into constraints set constr_id = 'S', infl_id = 33;
insert into constraints set constr_id = 'S', infl_id = 34;
insert into constraints set constr_id = 'S', infl_id = 37;
insert into constraints set constr_id = 'S', infl_id = 38;

-- Singular (pronouns)
insert into constraints set constr_id = 'S', infl_id = 41;
insert into constraints set constr_id = 'S', infl_id = 42;
insert into constraints set constr_id = 'S', infl_id = 45;
insert into constraints set constr_id = 'S', infl_id = 46;

-- Plural (nouns)
insert into constraints set constr_id = 'P', infl_id = 3;
insert into constraints set constr_id = 'P', infl_id = 4;
insert into constraints set constr_id = 'P', infl_id = 7;
insert into constraints set constr_id = 'P', infl_id = 8;
insert into constraints set constr_id = 'P', infl_id = 11;
insert into constraints set constr_id = 'P', infl_id = 12;
insert into constraints set constr_id = 'P', infl_id = 15;
insert into constraints set constr_id = 'P', infl_id = 16;
insert into constraints set constr_id = 'P', infl_id = 19;
insert into constraints set constr_id = 'P', infl_id = 20;
insert into constraints set constr_id = 'P', infl_id = 23;
insert into constraints set constr_id = 'P', infl_id = 24;

-- Plural (adjectives)
insert into constraints set constr_id = 'P', infl_id = 27;
insert into constraints set constr_id = 'P', infl_id = 28;
insert into constraints set constr_id = 'P', infl_id = 31;
insert into constraints set constr_id = 'P', infl_id = 32;
insert into constraints set constr_id = 'P', infl_id = 35;
insert into constraints set constr_id = 'P', infl_id = 36;
insert into constraints set constr_id = 'P', infl_id = 39;
insert into constraints set constr_id = 'P', infl_id = 40;

-- Plural (pronouns)
insert into constraints set constr_id = 'P', infl_id = 43;
insert into constraints set constr_id = 'P', infl_id = 44;
insert into constraints set constr_id = 'P', infl_id = 47;
insert into constraints set constr_id = 'P', infl_id = 48;

-- Plural (verbs)
insert into constraints set constr_id = 'P', infl_id = 49;
insert into constraints set constr_id = 'P', infl_id = 50;
insert into constraints set constr_id = 'P', infl_id = 52;
insert into constraints set constr_id = 'P', infl_id = 53;
insert into constraints set constr_id = 'P', infl_id = 57;
insert into constraints set constr_id = 'P', infl_id = 58;
insert into constraints set constr_id = 'P', infl_id = 59;
insert into constraints set constr_id = 'P', infl_id = 63;
insert into constraints set constr_id = 'P', infl_id = 64;
insert into constraints set constr_id = 'P', infl_id = 65;
insert into constraints set constr_id = 'P', infl_id = 69;
insert into constraints set constr_id = 'P', infl_id = 70;
insert into constraints set constr_id = 'P', infl_id = 71;
insert into constraints set constr_id = 'P', infl_id = 75;
insert into constraints set constr_id = 'P', infl_id = 76;
insert into constraints set constr_id = 'P', infl_id = 77;
insert into constraints set constr_id = 'P', infl_id = 81;
insert into constraints set constr_id = 'P', infl_id = 82;
insert into constraints set constr_id = 'P', infl_id = 83;

-- Impersonal (verbs)
insert into constraints set constr_id = 'U', infl_id = 49;
insert into constraints set constr_id = 'U', infl_id = 50;
insert into constraints set constr_id = 'U', infl_id = 52;
insert into constraints set constr_id = 'U', infl_id = 53;
insert into constraints set constr_id = 'U', infl_id = 56;
insert into constraints set constr_id = 'U', infl_id = 59;
insert into constraints set constr_id = 'U', infl_id = 62;
insert into constraints set constr_id = 'U', infl_id = 65;
insert into constraints set constr_id = 'U', infl_id = 68;
insert into constraints set constr_id = 'U', infl_id = 71;
insert into constraints set constr_id = 'U', infl_id = 74;
insert into constraints set constr_id = 'U', infl_id = 77;
insert into constraints set constr_id = 'U', infl_id = 80;
insert into constraints set constr_id = 'U', infl_id = 83;

-- Unipersonal (verbs)
insert into constraints set constr_id = 'I', infl_id = 49;
insert into constraints set constr_id = 'I', infl_id = 50;
insert into constraints set constr_id = 'I', infl_id = 52;
insert into constraints set constr_id = 'I', infl_id = 53;
insert into constraints set constr_id = 'I', infl_id = 56;
insert into constraints set constr_id = 'I', infl_id = 62;
insert into constraints set constr_id = 'I', infl_id = 68;
insert into constraints set constr_id = 'I', infl_id = 74;
insert into constraints set constr_id = 'I', infl_id = 80;

-- Past tenses (verbs)
insert into constraints set constr_id = 'T', infl_id = 49;
insert into constraints set constr_id = 'T', infl_id = 50;
insert into constraints set constr_id = 'T', infl_id = 52;
insert into constraints set constr_id = 'T', infl_id = 53;

insert into constraints set constr_id = 'T', infl_id = 66;
insert into constraints set constr_id = 'T', infl_id = 67;
insert into constraints set constr_id = 'T', infl_id = 68;
insert into constraints set constr_id = 'T', infl_id = 69;
insert into constraints set constr_id = 'T', infl_id = 70;
insert into constraints set constr_id = 'T', infl_id = 71;

insert into constraints set constr_id = 'T', infl_id = 72;
insert into constraints set constr_id = 'T', infl_id = 73;
insert into constraints set constr_id = 'T', infl_id = 74;
insert into constraints set constr_id = 'T', infl_id = 75;
insert into constraints set constr_id = 'T', infl_id = 76;
insert into constraints set constr_id = 'T', infl_id = 77;

insert into constraints set constr_id = 'T', infl_id = 78;
insert into constraints set constr_id = 'T', infl_id = 79;
insert into constraints set constr_id = 'T', infl_id = 80;
insert into constraints set constr_id = 'T', infl_id = 81;
insert into constraints set constr_id = 'T', infl_id = 82;
insert into constraints set constr_id = 'T', infl_id = 83;

-- Create invariable and temporary models
insert into models set model_id = 1, model_type = 'I', model_no = '1';
insert into models set model_id = 2, model_type = 'T', model_no = '1';

insert into transforms set transf_id = 1, transf_from = '', transf_to='';

insert into model_description set md_model = 1, md_infl = 84, md_variant = 0,
  md_order = 0, md_transf = 1, md_accent_shift = 101;
insert into model_description set md_model = 2, md_infl = 84, md_variant = 0,
  md_order = 0, md_transf = 1, md_accent_shift = 101;

insert into model_exponents set model_type = 'I', model_no = '1',
  form = 'invariabil';
insert into model_exponents set model_type = 'T', model_no = '1',
  form = 'invariabil';

insert into model_exponents set model_type = 'V', model_no = '2',
  form = 'circula';
insert into model_exponents set model_type = 'V', model_no = '6',
  form = 'merita';
insert into model_exponents set model_type = 'V', model_no = '7',
  form = 'aplauda';
insert into model_exponents set model_type = 'V', model_no = '9',
  form = 'amâna';
insert into model_exponents set model_type = 'V', model_no = '14',
  form = 'critica';
insert into model_exponents set model_type = 'V', model_no = '15',
  form = 'fumega';
insert into model_exponents set model_type = 'V', model_no = '19',
  form = 'clătina';
insert into model_exponents set model_type = 'V', model_no = '73',
  form = 'încăleca';
insert into model_exponents set model_type = 'V', model_no = '74',
  form = 'adăuga';
insert into model_exponents set model_type = 'V', model_no = '103',
  form = 'apropia';
insert into model_exponents set model_type = 'V', model_no = '105',
  form = 'mângâia';
insert into model_exponents set model_type = 'V', model_no = '510',
  form = 'prevedea';
insert into model_exponents set model_type = 'V', model_no = '606',
  form = 'abate';
insert into model_exponents set model_type = 'V', model_no = '643',
  form = 'conduce';
insert into model_exponents set model_type = 'V', model_no = '644',
  form = 'zice';
insert into model_exponents set model_type = 'V', model_no = '648',
  form = 'readuce';
