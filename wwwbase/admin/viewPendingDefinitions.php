<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Definiții nemoderate');

$sourceId = 0;
$sourceUrlName = util_getRequestParameter('source');
if ( $sourceUrlName ) {
  $source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
  $sourceId = $source ? $source->id : 0;
  SmartyWrap::assign('src_selected', $sourceId);
}

$ip = $_SERVER['REMOTE_ADDR'];
$defs = Definition::searchModerator('*', '', $sourceId, Definition::ST_PENDING, 0, 0, time(), 1, 500);
$searchResults = SearchResult::mapDefinitionArray($defs);
FileCache::putModeratorQueryResults($ip, $searchResults);

SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/viewPendingDefinitions.tpl');
?>
