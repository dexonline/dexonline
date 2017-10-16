<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT);
Util::assertNotMirror();

define('SECONDS_PER_DAY', 86400);
define('RESULTS_PER_PAGE', 500);

$name = Request::get('name');
$status = Request::get('status');
$nick = Request::get('nick');
$sourceId = Request::get('sourceId');
$startDate = Request::get('startDate');
$endDate = Request::get('endDate');
$page = Request::get('page', 1);
$prevPageButton = Request::has('prevPageButton');
$nextPageButton = Request::has('nextPageButton');
$searchButton = Request::has('searchButton');

$ip = $_SERVER['REMOTE_ADDR'];

// Execute query and display results
// Convert wildcards to mysql format
if ($searchButton || $prevPageButton || $nextPageButton) {
  if ($prevPageButton && $page > 1) {
    $page--;
  }
  if ($nextPageButton) {
    $page++;
  }
  $name = StringUtil::cleanupQuery($name);
  $arr = StringUtil::analyzeQuery($name);
  $hasDiacritics = $arr[0];
  $hasRegexp = $arr[1];
  $isAllDigits = $arr[2];
  $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
  
  $userId = '';
  if ($nick) {
    $user = User::get_by_nick($nick);
    if ($user) {
      $userId = $user->id;
    }
  }

  if ($startDate) {
    $startTs = DateTime::createFromFormat('Y-m-d', $startDate)->getTimestamp();
  } else {
    $startTs = 0;
  }

  if ($endDate) {
    $endTs = SECONDS_PER_DAY + DateTime::createFromFormat('Y-m-d', $endDate)->getTimestamp();
  } else {
    $endTs = 4000000000;
  }

  // Query the database and output the results
  $defs = Definition::searchModerator($name, $hasDiacritics, $sourceId, $status, $userId,
                                      $startTs, $endTs, $page, RESULTS_PER_PAGE);
  $searchResults = SearchResult::mapDefinitionArray($defs);

  $args = [
    'name' => $name,
    'status' => $status,
    'nick' => $nick,
    'sourceId' => $sourceId,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'page' => $page
  ];

  FileCache::putModeratorQueryResults($ip, [$searchResults, $args]);
} else {
  list($searchResults, $args) = FileCache::getModeratorQueryResults($ip);
}

SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('args', $args);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/definitionLookup.tpl');
?>
