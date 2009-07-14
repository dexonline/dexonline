<?php
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();
RecentLink::createOrUpdate('Definiții nemoderate');

$ip = $_SERVER['REMOTE_ADDR'];
$defs = Definition::searchModerator('*', '', 0, ST_PENDING, 0, 0, time());
$searchResults = SearchResult::mapDefinitionArray($defs);
fileCache_putModeratorQueryResults($ip, $searchResults);

smarty_assign('searchResults', $searchResults);
smarty_assign('sectionTitle', 'Definiții nemoderate');
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/definitionList.ihtml');
?>
