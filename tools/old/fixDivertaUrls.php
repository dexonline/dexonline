<?

$PREFIX = "http://www.dol.ro/magazin/";
$PREFIX_PREG = "http:\/\/www.dol.ro\/magazin\/";

require_once('../phplib/util.php');
require_once('../phplib/ads/adsModule.php');
require_once('../phplib/ads/diverta/divertaAdsModule.php');

$books = db_find(new DivertaBook(), "1 order by id");
foreach ($books as $book) {
  print "Loaded: {$book->id} [{$book->url}]\n";
  $matches = array();
  $result = preg_match("/^$PREFIX_PREG([^\/]+)\/(.*)/", $book->url, $matches);
  $category = strtolower($matches[1]);
  $book->url = "{$PREFIX}{$category}/{$matches[2]}";
  $book->save();
  print "Edited: {$book->id} [{$book->url}]\n";
}
?>
