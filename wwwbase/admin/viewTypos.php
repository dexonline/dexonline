<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Greșeli de tipar');

$sourceClause = '';
$sourceId = 0;
$sourceUrlName = util_getRequestParameter('source');
if ($sourceUrlName) {
  $source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
  $sourceId = $source ? $source->id : 0;
  $sourceClause = $source ? "sourceId = {$sourceId} and " : '';
  smarty_assign('src_selected', $sourceId);
}

$defs = Model::factory('Definition')
->raw_query("select * from Definition where {$sourceClause} id in (select definitionId from Typo) order by lexicon", null)->find_many();

smarty_assign('searchResults', SearchResult::mapDefinitionArray($defs));
smarty_assign('sectionTitle', 'Definiții cu greșeli de tipar');
smarty_assign('sectionCount', count($defs));
smarty_assign('sectionSources', true);
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayAdminPage('admin/definitionList.ihtml');

?>
