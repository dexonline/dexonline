rename table wordlist to InflectedForm;
alter table InflectedForm add id int not null auto_increment first,
  add primary key(id),
  change wl_form form char(50) not null,
  change wl_neaccentuat formNoAccent char(50) not null,
  change wl_utf8_general formUtf8General char(50) character set utf8 not null,
  change wl_lexem lexemId int not null,
  change wl_analyse inflectionId int not null,
  change wl_variant variant int not null;

rename table LOC_4_1.wordlist to LOC_4_1.InflectedForm;
alter table LOC_4_1.InflectedForm add id int not null auto_increment first,
  add primary key(id),
  change wl_form form char(50) not null,
  change wl_neaccentuat formNoAccent char(50) not null,
  change wl_utf8_general formUtf8General char(50) character set utf8 not null,
  change wl_lexem lexemId int not null,
  change wl_analyse inflectionId int not null,
  change wl_variant variant int not null;

rename table LOC_4_0.wordlist to LOC_4_0.InflectedForm;
alter table LOC_4_0.InflectedForm add id int not null auto_increment first,
  add primary key(id),
  change wl_form form char(50) not null,
  change wl_neaccentuat formNoAccent char(50) not null,
  change wl_utf8_general formUtf8General char(50) character set utf8 not null,
  change wl_lexem lexemId int not null,
  change wl_analyse inflectionId int not null,
  change wl_variant variant int not null;
