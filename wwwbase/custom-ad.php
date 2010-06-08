<?
require_once("../phplib/util.php");
require_once("../phplib/ads/adsModule.php");

$provider = util_getRequestParameter('provider');
require_once("../phplib/ads/{$provider}/{$provider}AdsModule.php");

if ($provider == 'diverta') {
  $bookId = util_getRequestParameter('bookId');
  $book = DivertaBook::get("id = '{$bookId}'");
  // TODO: Fixme
  $book->url = "http://google.com/search?q={$book->id}";
  smarty_assign('book', $book);
  smarty_assign('hasImage', file_exists(util_getRootPath() . "wwwbase/img/diverta/thumb/{$book->sku}.jpg"));
}

smarty_display("ads/{$provider}.ihtml");

?>
