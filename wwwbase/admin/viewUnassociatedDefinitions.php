<?

require_once("../../phplib/util.php");
util_assertModeratorStatus();
util_assertNotMirror();
RecentLink::createOrUpdate('Definiții neasociate');

$defs = db_find(new Definition(), "status != 2 and id not in (select DefinitionId from LexemDefinitionMap)");

smarty_assign('searchResults', SearchResult::mapDefinitionArray($defs));
smarty_assign('sectionTitle', 'Definiții neasociate');
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/definitionList.ihtml');

?>
