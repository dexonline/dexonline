<?php
/**
 * Output two lists of words associated with some common dictionaries, once with diacritical
 * marks and once without them.
 **/

require_once __DIR__ . '/../lib/Core.php';

ini_set('memory_limit', '1G');

const STATIC_SERVER_DIR = '/download/scrabble';
const DEX09_ID = 27;
const MDN_ID = 21;

Log::notice('started');

Log::info('collecting forms');
$forms = Model::factory('InflectedForm')
  ->table_alias('if')
  ->select('if.formNoAccent')
  ->distinct()
  ->join('Inflection', ['if.inflectionId', '=', 'i.id'], 'i')
  ->join('Lexeme', ['if.lexemeId', '=', 'l.id'], 'l')
  ->join('Definition', ['l.formNoAccent', '=', 'd.lexicon'], 'd')
  ->where('i.animate', false)
  ->where_raw('binary if.formNoAccent rlike "^[a-zăâîșț]+$"') // no caps - chemical symbols etc.
  ->where_raw('char_length(if.formNoAccent) between 3 and 7')
  ->where('d.status', Definition::ST_ACTIVE)
  ->where_in('d.sourceId', [ DEX09_ID, MDN_ID ])
  ->where('if.apheresis', false)
  ->where('if.apocope', false)
  ->order_by_asc('if.formNoAccent')
  ->find_many();
$joined = implode("\n", Util::objectProperty($forms, 'formNoAccent'));

$diaFileName = Config::TEMP_DIR . 'game-word-list-dia.txt';
Log::info('writing forms to %s', $diaFileName);
file_put_contents($diaFileName, $joined);

$tmpFileName = Config::TEMP_DIR . 'game-word-list-tmp.txt';
$noDiaFileName = Config::TEMP_DIR . 'game-word-list.txt';
Log::info('writing Latin forms to %s', $noDiaFileName);
$latin = Str::unicodeToLatin($joined);
file_put_contents($tmpFileName, $latin);
exec("sort $tmpFileName | uniq > $noDiaFileName");

Log::info('uploading files to static server');
StaticUtil::move($diaFileName, 'download/game-word-list-dia.txt');
StaticUtil::move($noDiaFileName, 'download/game-word-list.txt');

// cleanup
unlink($tmpFileName);

Log::notice('finished');
