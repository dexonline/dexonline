alter table Lexem add hyphenations varchar(255) after verifSp, add pronounciations varchar(255) after hyphenations;
alter table Lexem add variantOf int after pronounciations, add index(variantOf);
