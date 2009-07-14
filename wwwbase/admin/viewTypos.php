<?
require_once("../../phplib/util.php");
util_assertModeratorStatus();
util_assertNotMirror();
RecentLink::createOrUpdate('Greșeli de tipar');

$defs = Definition::loadDefinitionsHavingTypos();

smarty_assign('searchResults', SearchResult::mapDefinitionArray($defs));
smarty_assign('sectionTitle', 'Definiții cu greșeli de tipar');
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/definitionList.ihtml');

?>
