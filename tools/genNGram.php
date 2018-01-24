<?php

require_once __DIR__ . '/../phplib/Core.php';
define('INSERT_SIZE', 10000);

ini_set('max_execution_time', '3600');
ini_set('memory_limit', '1G');

Log::notice('started');

$start = microtime(true);
DB::execute("truncate table NGram"); // This should be fast

$dbResult = DB::execute("select * from Lexem", PDO::FETCH_ASSOC);

$values = array();
foreach ($dbResult as $cnt => $row) {
  $lexeme = Model::factory('Lexeme')->create($row);
  $ngrams = NGram::split($lexeme->formNoAccent);

  foreach ($ngrams as $i => $ngram) {
    $values[] = array($ngram, $i, $lexeme->id);
  }
  if (count($values) >= INSERT_SIZE) {
    dumpValues($values);
    $values = array();
  }
  if ($cnt % 1000 == 0) {
    Log::info('%d lexemes processed, %0.3f lexemes/second.', $cnt, $cnt / (microtime(true) - $start));
  }
}
dumpValues($values);

Log::notice('finished');

/*********************************************************************/

function dumpValues($values) {
  // Assemble low-level MySQL query. Idiorm inserts records one by one, which is many times slower.
  $query = 'insert into NGram(ngram, pos, lexemeId) values ';
  foreach ($values as $i => $set) {
    if ($i) {
      $query .= ',';
    }
    $query .= sprintf("('%s', %d, %d)", addslashes($set[0]), $set[1], $set[2]);
  }
  DB::execute($query);
}
