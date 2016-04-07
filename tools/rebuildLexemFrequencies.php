<?php
// This is nowhere near perfect, but it's a decent approximation for starters.
// Gives each lexem a frequency between 0.00 and 1.00
// Stop words defined in stringUtil.php get 1.00
// Other lexems get frequencies distributed uniformly between 0.01 and 1.00 based on their percentile rankings in the full text index.
require_once __DIR__ . '/../phplib/util.php';
ini_set('max_execution_time', '3600');
ini_set('memory_limit', '256M');
assert_options(ASSERT_BAIL, 1);

Log::notice('started');

Log::info('Setting frequency to 1.00 for manual stop words');
$lexems = Lexem::get_all_by_stopWord(1);
foreach ($lexems as $l) {
  $l->frequency = 1.00;
  $l->save();
}

Log::info("Scanning full text index");
$dbResult = db_execute("select lexemId from FullTextIndex group by lexemId order by count(*)");
$numLexems = $dbResult->rowCount();
$i = 0;
foreach ($dbResult as $row) {
  $lexem = Lexem::get_by_id($row[0]);
  $lexem->frequency = round($i / $numLexems + 0.005, 2);
  $lexem->save();
  $i++;
  if ($i % 10000 == 0) {
    Log::info("$i of $numLexems labeled");
  }
}

Log::notice('finished');

?>
