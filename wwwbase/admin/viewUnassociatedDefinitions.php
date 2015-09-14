<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
RecentLink::createOrUpdate('Definiții neasociate');

$defs = Model::factory('Definition')->where_raw("status != 2 and id not in (select definitionId from LexemDefinitionMap)")->find_many();

SmartyWrap::assign('searchResults', SearchResult::mapDefinitionArray($defs));
SmartyWrap::assign('sectionTitle', 'Definiții neasociate');
SmartyWrap::assign('sectionCount', count($defs));
SmartyWrap::assign('allStatuses', util_getAllStatuses());
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/definitionList.tpl');

?>
