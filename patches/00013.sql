-- Replace ş with ș and ţ with ț in all lexems and wordlists.
-- We fixed the code so that these characters can no longer appear in lexems, but we never fixed the existing instances.
update lexems set lexem_forma = replace(lexem_forma, 'ş', 'ș'), lexem_neaccentuat = replace(lexem_neaccentuat, 'ş', 'ș'), lexem_utf8_general = replace(lexem_utf8_general, 'ş', 'ș'), lexem_invers = replace(lexem_invers, 'ş', 'ș');
update lexems set lexem_forma = replace(lexem_forma, 'ţ', 'ț'), lexem_neaccentuat = replace(lexem_neaccentuat, 'ţ', 'ț'), lexem_utf8_general = replace(lexem_utf8_general, 'ţ', 'ț'), lexem_invers = replace(lexem_invers, 'ţ', 'ț');
update wordlist set wl_form = replace(wl_form, 'ş', 'ș'), wl_neaccentuat = replace(wl_neaccentuat, 'ş', 'ș'), wl_utf8_general = replace(wl_utf8_general, 'ş', 'ș');
update wordlist set wl_form = replace(wl_form, 'ţ', 'ț'), wl_neaccentuat = replace(wl_neaccentuat, 'ţ', 'ț'), wl_utf8_general = replace(wl_utf8_general, 'ţ', 'ț');
