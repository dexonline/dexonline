<?php

/**
 * Change lexeme model types from F to IL where applicable.
 **/

require_once __DIR__ . '/../phplib/Core.php';

const LONG_INF_MODELS = ['107', '113'];
const MORPHOLOGIC_SOURCE_TYPE_ID = 2;
const FEMININE_NOUN_TAG_ID = 51;
const LONG_INFINITIVE_TAG_ID = 351;

$lexemes = Model::factory('Lexem')
         ->where('modelType', 'F')
         ->where_in('modelNumber', LONG_INF_MODELS)
         ->where_like('formNoAccent', '%re')
         ->order_by_asc('formNoAccent')
         ->find_many();

foreach ($lexemes as $l) {
  // Only change the type for lexemes associated with the verb's definitions.
  // Skip those that have their own definitions.

  $numDefs = Model::factory('Definition')
           ->table_alias('d')
           ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
           ->join('EntryLexem', ['ed.entryId', '=', 'el.entryId'], 'el')
           ->join('Source', ['d.sourceId', '=', 's.id'], 's')
           ->where_in('d.status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
           ->where_not_equal('s.sourceTypeId', MORPHOLOGIC_SOURCE_TYPE_ID)
           ->where('d.lexicon', $l->formNoAccent)
           ->where('el.lexemId', $l->id)
           ->count();
  if (!$numDefs) {
    print("procesez $l\n");
    $l->modelType = 'IL';
    $l->save();
    ObjectTag::dissociate(ObjectTag::TYPE_LEXEM, $l->id, FEMININE_NOUN_TAG_ID);
    ObjectTag::associate(ObjectTag::TYPE_LEXEM, $l->id, LONG_INFINITIVE_TAG_ID);
  } else {
    print("ignor $l\n");
  }
}
