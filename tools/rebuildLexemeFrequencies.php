<?php
// This is nowhere near perfect, but it's a decent approximation for starters.
// Gives each lexeme a frequency between 0.00 and 1.00
// Stop words defined in stringUtil.php get 1.00
// Other lexemes get frequencies distributed uniformly between 0.01 and 1.00 based on their percentile rankings in the full text index.
require_once __DIR__ . '/../phplib/Core.php';
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '256M');
assert_options(ASSERT_BAIL, 1);

Log::notice('started');

Log::info('Setting frequency to 1.00 for manual stop words');
$lexemes = Lexeme::get_all_by_stopWord(1);
foreach ($lexemes as $l) {
  $l->frequency = 1.00;
  $l->save();
}

Log::info("Scanning full text index");
$dbResult = DB::execute("select lexemeId from FullTextIndex group by lexemeId order by count(*)");
$numLexemes = $dbResult->rowCount();
$i = 0;
foreach ($dbResult as $row) {
  $lexeme = Lexeme::get_by_id($row[0]);
  $lexeme->frequency = round($i / $numLexemes + 0.005, 2);
  $lexeme->save();
  $i++;
  if ($i % 10000 == 0) {
    Log::info("$i of $numLexemes labeled");
  }
}

Log::notice('finished');
