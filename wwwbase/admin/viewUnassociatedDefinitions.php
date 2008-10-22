<?

require_once("../../phplib/util.php");
util_assertModeratorStatus();
util_assertNotMirror();
RecentLink::createOrUpdate('Definiţii neasociate');

$defs = Definition::loadUnassociated();

smarty_assign('searchResults', SearchResult::mapDefinitionArray($defs));
smarty_assign('sectionTitle', 'Definiţii neasociate');
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/definitionList.ihtml');

?>
