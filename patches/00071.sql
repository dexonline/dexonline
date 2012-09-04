alter table Lexem add charLength int after reverse, add index (charLength);
update Lexem set charLength = char_length(formNoAccent);
