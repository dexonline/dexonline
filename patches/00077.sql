-- Fix diacritics in DefinitionSimple
update DefinitionSimple set definition = replace(definition, 'Ş', 'Ș');
update DefinitionSimple set definition = replace(definition, 'ş', 'ș');
update DefinitionSimple set definition = replace(definition, 'Ţ', 'Ț');
update DefinitionSimple set definition = replace(definition, 'ţ', 'ț');
