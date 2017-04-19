<?php
// TODO: Convert to Idiorm if this is ever needed again
require_once('phplib/util.php');
require_once('phplib/ads/adsModule.php');
require_once('phplib/ads/diverta/divertaAdsModule.php');

$books = Model::factory('DivertaBook')->order_by_asc('id')->find_many();
$numBooks = count($books);
print "Reindexing $numBooks book titles.\n";
foreach ($books as $i => $book) {
  DB::execute("delete from diverta_Index where bookId = {$book->id}");
  $hasDiacritics = StringUtil::hasDiacritics($book->title);
  $title = mb_strtolower($book->title);
  $title = str_replace(array(',', '.'), '', $title);
  $titleWords = preg_split("/\\s+/", $title);
  $lexemIds = array();

  foreach ($titleWords as $word) {
    if (!StringUtil::isStopWord($word, $hasDiacritics)) {
      $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
      $wordLexemIds = DB::getArray(DB::execute("select distinct lexemId from InflectedForm where $field = '" . addslashes($word) . "'"));
      foreach ($wordLexemIds as $lexemId) {
        $lexemIds[$lexemId] = true;
      }
    }
  }

  foreach ($lexemIds as $lexemId => $ignored) {
    $index = new DivertaIndex();
    $index->lexemId = $lexemId;
    $index->bookId = $book->id;
    $index->save();
  }

  if ($i % 100 == 99) {
    print ($i + 1) . " titles indexed.\n";
  }
}

?>
