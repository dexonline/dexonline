<?php
/**
 * Output four lists of words associated with some common dictionaries, one
 * for each Hangman difficulty.
 **/

require_once __DIR__ . '/../lib/Core.php';

const FILE_NAME = 'download/word-list-hangman-%d.txt';
const LEVELS = [
  1 => [ 0.90, 1.00 ],
  2 => [ 0.80, 0.89 ],
  3 => [ 0.68, 0.79 ],
  4 => [ 0.00, 0.67 ],
];

Log::notice('started');

Log::info('collecting lexemes');
$forms = Model::factory('Lexeme')
  ->table_alias('l')
  ->select('l.formNoAccent')
  ->select_expr('max(l.frequency)', 'freq')
  ->distinct()
  ->join('EntryLexeme', ['el.lexemeId', '=', 'l.id'], 'el')
  ->join('Entry', ['e.id', '=', 'el.entryId'], 'e')
  ->join('EntryDefinition', ['ed.entryId', '=', 'e.id'], 'ed')
  ->join('Definition', ['d.id', '=', 'ed.definitionId'], 'd')
  ->join('Source', ['s.id', '=', 'd.sourceId'], 's')
  ->where_raw('l.formNoAccent rlike "^[a-zăâîșț]+$"')
  ->where_raw('char_length(l.formNoAccent) >= 5')
  ->where('el.main', true)
  ->where('e.adult', false)
  ->where('d.status', Definition::ST_ACTIVE)
  ->where('s.type', Source::TYPE_OFFICIAL)
  ->group_by('l.formNoAccent')
  ->order_by_asc('l.formNoAccent')
  ->find_array();

$lists = [
  1 => [], 2 => [], 3 => [], 4 => [],
];

foreach ($forms as $rec) {
  $dif = 1;
  while ($rec['freq'] < LEVELS[$dif][0]) {
    $dif++;
  }
  $lists[$dif][] = $rec['formNoAccent'];
}

for ($dif = 1; $dif <= 4; $dif++) {
  $fileName = sprintf(FILE_NAME, $dif);
  Log::info('writing forms to %s for difficulty %d', $fileName, $dif);
  $joined = implode("\n", $lists[$dif]);
  StaticUtil::putContents($joined, $fileName);
}

Log::notice('finished, peak memory usage %d', memory_get_peak_usage());
