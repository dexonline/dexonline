<?php

/**
 * Change lexeme model types from A to PT where applicable.
 **/

require_once __DIR__ . '/../phplib/Core.php';

const PARTICIPLE_INFLECTION_ID = 52;
const MORPHOLOGIC_SOURCE_TYPE_ID = 2;
const ADJECTIVE_TAG_ID = 45;
const PARTICIPLE_TAG_ID = 352;

// find lexemes for which there exists a verb that generates this participle form
$lexemes = Model::factory('Lexem')
         ->table_alias('l')
         ->select('l.*')
         ->distinct()
         ->join('InflectedForm', ['l.formNoAccent', '=', 'i.formNoAccent'], 'i')
         ->where('modelType', 'A')
         ->where('i.inflectionId', PARTICIPLE_INFLECTION_ID)
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
    $l->modelType = 'PT';
    $l->save();
    ObjectTag::dissociate(ObjectTag::TYPE_LEXEM, $l->id, ADJECTIVE_TAG_ID);
    ObjectTag::associate(ObjectTag::TYPE_LEXEM, $l->id, PARTICIPLE_TAG_ID);
  } else {
    print("ignor $l\n");
  }
}
