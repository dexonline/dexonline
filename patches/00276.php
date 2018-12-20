<?php

$lexemes = Model::factory('Lexeme')
  ->where_in('modelType', ['V', 'VT'])
  ->where('hasApheresis', true)
  ->where('staleParadigm', true)
  ->find_many();

foreach ($lexemes as $l) {
  Log::info('Regenerating %s (%s%s) with dependent lexemes',
            $l->form, $l->modelType, $l->modelNumber);
  // Post-mortem: this was BAD. Lexeme::deepSave() deletes existing DB tags.
  $l->deepSave();
  $l->regenerateDependentLexemes();
}
