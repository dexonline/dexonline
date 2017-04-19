<?php

require_once __DIR__ . '/../phplib/util.php';
define('INSERT_SIZE', 10000);

ini_set('max_execution_time', '3600');
ini_set('memory_limit', '1G');

Log::notice('started');

$start = microtime(true);
DB::execute("truncate table NGram"); // This should be fast

$dbResult = DB::execute("select * from Lexem", PDO::FETCH_ASSOC);

$values = array();
foreach ($dbResult as $cnt => $row) {
  $lexem = Model::factory('Lexem')->create($row);
  $ngrams = NGram::split($lexem->formNoAccent);

  foreach ($ngrams as $i => $ngram) {
    $values[] = array($ngram, $i, $lexem->id);
  }
  if (count($values) >= INSERT_SIZE) {
    dumpValues($values);
    $values = array();
  }
  if ($cnt % 1000 == 0) {
    Log::info('%d lexems processed, %0.3f lexems/second.', $cnt, $cnt / (microtime(true) - $start));
  }
}
dumpValues($values);

Log::notice('finished');

/*********************************************************************/

function dumpValues($values) {
  // Assemble low-level MySQL query. Idiorm inserts records one by one, which is many times slower.
  $query = 'insert into NGram(ngram, pos, lexemId) values ';
  foreach ($values as $i => $set) {
    if ($i) {
      $query .= ',';
    }
    $query .= sprintf("('%s', %d, %d)", addslashes($set[0]), $set[1], $set[2]);
  }
  DB::execute($query);
}

?>
