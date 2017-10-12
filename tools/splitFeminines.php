<?php

/**
 * Splits some weird feminine noun models (F156, F157, F158).
 **/

require_once __DIR__ . '/../phplib/Core.php';

const MIGRATE = [
  '156' => [ '151', '39' ],
  '157' => [ '153', '39' ],
  '158' => [ '154', '39' ],
];

const BASE_INFLECTION_ID = 9; // singular nominative-accusative, no article

$lexemes = Model::factory('Lexem')
         ->where('modelType', 'F')
         ->where_in('modelNumber', array_keys(MIGRATE))
         ->order_by_asc('formNoAccent')
         ->find_many();

foreach ($lexemes as $l) {
  $newNumbers = MIGRATE[$l->modelNumber];
  $forms = Model::factory('InflectedForm')
         ->where('lexemId', $l->id)
         ->where('inflectionId', BASE_INFLECTION_ID)
         ->order_by_asc('variant')
         ->find_many();
 
  printf("** Sparg %s (F%s) în %s (F%s) + %s (F%s)\n",
         StringUtil::pad($l->form, 13), $l->modelNumber,
         StringUtil::pad($forms[0]->form, 13), $newNumbers[0],
         StringUtil::pad($forms[1]->form, 13), $newNumbers[1]);

  cloneLexeme($l, $forms[1]->form, $newNumbers[1]);

  // reuse the lexeme for variant 0
  $l->modelNumber = $newNumbers[0];
  $l->save();
  $l->regenerateParadigm();
}


// different use case than Lexem::_clone()
function cloneLexeme($l, $form, $modelNumber) {
  $c = $l->parisClone();
  $c->setForm($form);
  $c->modelNumber = $modelNumber;
  $c->save();

  // copy sources, entries and tags
  LexemSource::copy($l->id, $c->id, 1);
  EntryLexem::copy($l->id, $c->id, 2);
  foreach ($l->getObjectTags() as $ot) {
    ObjectTag::associate(ObjectTag::TYPE_LEXEM, $c->id, $ot->tagId);
  }

  // only now can we regenerate the paradigm, because certain tags dictate paradigm forms (e.g.
  // [admite vocativul] allows vocative forms.
  $c->regenerateParadigm();

  return $c;
}
