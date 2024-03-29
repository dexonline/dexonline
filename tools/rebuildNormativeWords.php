<?php
/**
 * Outputs a list of words associated with normative dictionaries.
 **/

require_once __DIR__ . '/../lib/Core.php';

ini_set('memory_limit', '256M');

Log::notice('started');

Log::info('collecting forms');
$forms = Model::factory('Lexeme')
  ->table_alias('l')
  ->select('l.formNoAccent')
  ->distinct()
  ->join('EntryLexeme', ['el.lexemeId', '=', 'l.id'], 'el')
  ->join('EntryDefinition', ['ed.entryId', '=', 'el.entryId'], 'ed')
  ->join('Definition', ['d.id', '=', 'ed.definitionId'], 'd')
  ->join('Source', ['s.id', '=', 'd.sourceId'], 's')
  ->where_not_equal('l.modelType', 'i')
  ->where('d.status', Definition::ST_ACTIVE)
  ->where('s.normative', true)
  ->order_by_asc('l.formNoAccent')
  ->find_many();

$joined = implode("\n", Util::objectProperty($forms, 'formNoAccent'));

Log::info('writing forms to download/normative-words.txt');
StaticUtil::putContents($joined, 'download/normative-words.txt');

Log::notice('finished');
