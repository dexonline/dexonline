<?php
header('Content-type: application/x-javascript');
require_once("../phplib/util.php");
require_once("../phplib/ads/adsModule.php");

$provider = util_getRequestParameter('provider'); // Display a banner for this provider
$go = util_getRequestParameter('go');             // Track a click and redirect to this provider
$clickurl = util_getRequestParameter('clickurl'); // Sent to us by OpenX; when displaying a banner, we have to link to this URL
if ($go) {
  $provider = $go;
  $go = true;
}
require_once("../phplib/ads/{$provider}/{$provider}AdsModule.php");

if ($provider == 'diverta') {
  $bookId = util_getRequestParameter('bookId');
  $book = DivertaBook::get_by_id($bookId);
  if (!$book) {
    exit;
  }
  if ($go) {
    $book->clicks++;
    $book->save();
    util_redirect($book->url);
  }
  $book->impressions++;
  $book->save();
  SmartyWrap::assign('book', $book);
  SmartyWrap::assign('hasImage', file_exists(util_getRootPath() . "wwwbase/img/diverta/thumb/{$book->sku}.jpg"));
}

SmartyWrap::assign('clickurl', str_replace('__', '&', $clickurl));
$output = SmartyWrap::fetch("ads/{$provider}.tpl");
$output = addslashes(str_replace("\n", ' ', $output));
print "document.write(\"{$output}\");";

?>
