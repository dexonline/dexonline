drop table if exists models;
create table models (
  model_id int not null auto_increment,
  model_type int  not null,
  model_no int not null,
  model_descr text,
  primary key (model_id), 
  key morf_index (model_type, model_no)
);

drop table if exists model_description;
create table model_description (
  md_id int not null auto_increment,
  md_model int not null,
  md_infl int not null,
  md_transf int,
  primary key (md_id),
  unique key (md_model, md_infl),
  key (md_transf)
);

drop table if exists lexems;
create table if not exists lexems (
  lexem_id int not null auto_increment,
  lexem_forma varchar(50),
  lexem_model int not null,
  primary key (lexem_id),
  key (lexem_model)
);

drop table if exists wordlist;
create table if not exists wordlist (
  wl_id int not null auto_increment,
  wl_form varchar(50) not null,
  wl_lexem int,
  wl_analyse int,
  primary key (wl_id),
  key (wl_lexem),
  key (wl_analyse)
);
