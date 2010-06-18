-- Replace ş with ș and ţ with ț in all lexems and wordlists in LOC databases.
update LOC_4_0.Lexem set form = replace(form, 'ş', 'ș'), formNoAccent = replace(formNoAccent, 'ş', 'ș'), formUtf8General = replace(formUtf8General, 'ş', 'ș'), reverse = replace(reverse, 'ş', 'ș');
update LOC_4_0.Lexem set form = replace(form, 'ţ', 'ț'), formNoAccent = replace(formNoAccent, 'ţ', 'ț'), formUtf8General = replace(formUtf8General, 'ţ', 'ț'), reverse = replace(reverse, 'ţ', 'ț');
update LOC_4_0.InflectedForm set form = replace(form, 'ş', 'ș'), formNoAccent = replace(formNoAccent, 'ş', 'ș'), formUtf8General = replace(formUtf8General, 'ş', 'ș');
update LOC_4_0.InflectedForm set form = replace(form, 'ţ', 'ț'), formNoAccent = replace(formNoAccent, 'ţ', 'ț'), formUtf8General = replace(formUtf8General, 'ţ', 'ț');

update LOC_4_1.Lexem set form = replace(form, 'ş', 'ș'), formNoAccent = replace(formNoAccent, 'ş', 'ș'), formUtf8General = replace(formUtf8General, 'ş', 'ș'), reverse = replace(reverse, 'ş', 'ș');
update LOC_4_1.Lexem set form = replace(form, 'ţ', 'ț'), formNoAccent = replace(formNoAccent, 'ţ', 'ț'), formUtf8General = replace(formUtf8General, 'ţ', 'ț'), reverse = replace(reverse, 'ţ', 'ț');
update LOC_4_1.InflectedForm set form = replace(form, 'ş', 'ș'), formNoAccent = replace(formNoAccent, 'ş', 'ș'), formUtf8General = replace(formUtf8General, 'ş', 'ș');
update LOC_4_1.InflectedForm set form = replace(form, 'ţ', 'ț'), formNoAccent = replace(formNoAccent, 'ţ', 'ț'), formUtf8General = replace(formUtf8General, 'ţ', 'ț');
