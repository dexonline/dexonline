<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Definiții nemoderate');

$sourceId = 0;
$sourceUrlName = util_getRequestParameter('source');
if ( $sourceUrlName ) {
  $source = $sourceUrlName ? Source::get("urlName='$sourceUrlName'") : null;
  $sourceId = $source ? $source->id : 0;
  smarty_assign('src_selected', $sourceId);
}

$ip = $_SERVER['REMOTE_ADDR'];
$defs = Definition::searchModerator('*', '', $sourceId, ST_PENDING, 0, 0, time());
$searchResults = SearchResult::mapDefinitionArray($defs);
fileCache_putModeratorQueryResults($ip, $searchResults);

smarty_assign('searchResults', $searchResults);
smarty_assign('sectionTitle', 'Definiții nemoderate');
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/definitionList.ihtml');
?>
