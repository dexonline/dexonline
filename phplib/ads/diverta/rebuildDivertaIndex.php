<?php
// TODO: Convert to Idiorm if this is ever needed again
require_once('phplib/Core.php');
require_once('phplib/ads/adsModule.php');
require_once('phplib/ads/diverta/divertaAdsModule.php');

$books = Model::factory('DivertaBook')->order_by_asc('id')->find_many();
$numBooks = count($books);
print "Reindexing $numBooks book titles.\n";
foreach ($books as $i => $book) {
  DB::execute("delete from diverta_Index where bookId = {$book->id}");
  $hasDiacritics = Str::hasDiacritics($book->title);
  $title = mb_strtolower($book->title);
  $title = str_replace(array(',', '.'), '', $title);
  $titleWords = preg_split("/\\s+/", $title);
  $lexemeIds = array();

  foreach ($titleWords as $word) {
    if (!Str::isStopWord($word, $hasDiacritics)) {
      $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
      $wordLexemeIds = DB::getArray(DB::execute("select distinct lexemeId from InflectedForm where $field = '" . addslashes($word) . "'"));
      foreach ($wordLexemeIds as $lexemeId) {
        $lexemeIds[$lexemeId] = true;
      }
    }
  }

  foreach ($lexemeIds as $lexemeId => $ignored) {
    $index = new DivertaIndex();
    $index->lexemeId = $lexemeId;
    $index->bookId = $book->id;
    $index->save();
  }

  if ($i % 100 == 99) {
    print ($i + 1) . " titles indexed.\n";
  }
}
