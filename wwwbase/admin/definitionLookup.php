<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Căutare definiții');

define('RESULTS_PER_PAGE', 500);

$name = util_getRequestParameter('name');
$status = util_getRequestIntParameter('status');
$nick = util_getRequestParameter('nick');
$sourceId = util_getRequestIntParameter('sourceId');
$yr1 = util_getRequestIntParameter('yr1');
$mo1 = util_getRequestIntParameter('mo1');
$da1 = util_getRequestIntParameter('da1');
$yr2 = util_getRequestIntParameter('yr2');
$mo2 = util_getRequestIntParameter('mo2');
$da2 = util_getRequestIntParameter('da2');
$page = util_getRequestIntParameterWithDefault('page', 1);
$prevPageButton = util_getRequestParameter('prevPageButton');
$nextPageButton = util_getRequestParameter('nextPageButton');
$searchButton = util_getRequestParameter('searchButton');

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
  $defs = Definition::searchModerator($name, $hasDiacritics, $sourceId, $status, $userId, $beginTime, $endTime, $page, RESULTS_PER_PAGE);
  $searchResults = SearchResult::mapDefinitionArray($defs);

  $args = array('name' => $name,
                'status' => $status,
                'nick' => $nick,
                'sourceId' => $sourceId,
                'yr1' => $yr1,
                'mo1' => $mo1,
                'da1' => $da1,
                'yr2' => $yr2,
                'mo2' => $mo2,
                'da2' => $da2,
                'page' => $page);

  FileCache::putModeratorQueryResults($ip, array($searchResults, $args));
} else {
  list($searchResults, $args) = FileCache::getModeratorQueryResults($ip);
}

SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('args', $args);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/definitionLookup.tpl');
?>
