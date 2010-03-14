rename table lexems to Lexem;
alter table Lexem
  change lexem_id id int not null auto_increment,
  change lexem_forma form char(50) not null,
  change lexem_neaccentuat formNoAccent char(50) not null,
  change lexem_utf8_general formUtf8General char(50) character set utf8 not null,
  change lexem_invers reverse char(50) not null,
  change lexem_descr description varchar(255) not null,
  change lexem_model_type modelType char(2) not null,
  change lexem_model_no modelNumber char(8) not null,
  change lexem_restriction restriction char(4) not null,
  drop lexem_parse_info,
  change lexem_comment comment text,
  change lexem_is_loc isLoc int not null,
  change lexem_no_accent noAccent int not null,
  change ModDate modDate int not null,
  change CreateDate createDate int not null;

rename table LOC_4_1.lexems to LOC_4_1.Lexem;
alter table LOC_4_1.Lexem
  change lexem_id id int not null auto_increment,
  change lexem_forma form char(50) not null,
  change lexem_neaccentuat formNoAccent char(50) not null,
  change lexem_utf8_general formUtf8General char(50) character set utf8 not null,
  change lexem_invers reverse char(50) not null,
  change lexem_descr description varchar(255) not null,
  change lexem_model_type modelType char(2) not null,
  change lexem_model_no modelNumber char(8) not null,
  change lexem_restriction restriction char(4) not null,
  drop lexem_parse_info,
  change lexem_comment comment text,
  change lexem_is_loc isLoc int not null,
  change lexem_no_accent noAccent int not null,
  change ModDate modDate int not null,
  change CreateDate createDate int not null;

rename table LOC_4_0.lexems to LOC_4_0.Lexem;
alter table LOC_4_0.Lexem
  change lexem_id id int not null auto_increment,
  change lexem_forma form char(50) not null,
  change lexem_neaccentuat formNoAccent char(50) not null,
  change lexem_utf8_general formUtf8General char(50) character set utf8 not null,
  change lexem_invers reverse char(50) not null,
  change lexem_descr description varchar(255) not null,
  change lexem_model_type modelType char(2) not null,
  change lexem_model_no modelNumber char(8) not null,
  change lexem_restriction restriction char(4) not null,
  drop lexem_parse_info,
  change lexem_comment comment text,
  change lexem_is_loc isLoc int not null,
  change lexem_no_accent noAccent int not null,
  change ModDate modDate int not null,
  change CreateDate createDate int not null;
