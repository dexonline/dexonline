<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Căutare definiții');

$name = util_getRequestParameter('name');
$status = util_getRequestIntParameter('status');
$nick = util_getRequestParameter('nick');
$sourceId = util_getRequestIntParameter('source');
$yr1 = util_getRequestIntParameter('yr1');
$mo1 = util_getRequestIntParameter('mo1');
$da1 = util_getRequestIntParameter('da1');
$yr2 = util_getRequestIntParameter('yr2');
$mo2 = util_getRequestIntParameter('mo2');
$da2 = util_getRequestIntParameter('da2');
$searchButton = util_getRequestParameter('searchButton');

$ip = $_SERVER['REMOTE_ADDR'];

// Execute query and display results
// Convert wildcards to mysql format
if ($searchButton) {
  $name = StringUtil::cleanupQuery($name);
  $arr = StringUtil::analyzeQuery($name);
  $hasDiacritics = $arr[0];
  $hasRegexp = $arr[1];
  $isAllDigits = $arr[2];
  $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
  
  $userId = '';
  if ($nick) {
    $user = User::get("nick = '$nick'");
    if ($user) {
      $userId = $user->id;
    }
  }
  $beginTime = mktime(0, 0, 0, $mo1, $da1, $yr1);
  $endTime = mktime(23, 59, 59, $mo2, $da2, $yr2);
  
  // Query the database and output the results
  $defs = Definition::searchModerator($name, $hasDiacritics, $sourceId, $status, $userId, $beginTime, $endTime);
  $searchResults = SearchResult::mapDefinitionArray($defs);
  fileCache_putModeratorQueryResults($ip, $searchResults);
} else {
  $searchResults = fileCache_getModeratorQueryResults($ip);
}

smarty_assign('searchResults', $searchResults);
smarty_assign('sectionTitle', 'Căutare definiții');
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/definitionList.ihtml');
?>
