<?php
require_once("../../phplib/util.php"); 
User::require(User::PRIV_EDIT);
util_assertNotMirror();

define('RESULTS_PER_PAGE', 500);

$name = Request::get('name');
$status = Request::get('status');
$nick = Request::get('nick');
$sourceId = Request::get('sourceId');
$yr1 = Request::get('yr1');
$mo1 = Request::get('mo1');
$da1 = Request::get('da1');
$yr2 = Request::get('yr2');
$mo2 = Request::get('mo2');
$da2 = Request::get('da2');
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
  $beginTime = mktime(0, 0, 0, $mo1, $da1, $yr1);
  $endTime = mktime(23, 59, 59, $mo2, $da2, $yr2);
  
  // Query the database and output the results
  $defs = Definition::searchModerator($name, $hasDiacritics, $sourceId, $status, $userId,
                                      $beginTime, $endTime, $page, RESULTS_PER_PAGE);
  $searchResults = SearchResult::mapDefinitionArray($defs);

  $args = [
    'name' => $name,
    'status' => $status,
    'nick' => $nick,
    'sourceId' => $sourceId,
    'yr1' => $yr1,
    'mo1' => $mo1,
    'da1' => $da1,
    'yr2' => $yr2,
    'mo2' => $mo2,
    'da2' => $da2,
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
