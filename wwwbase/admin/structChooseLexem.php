<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_STRUCT);
util_assertNotMirror();

// Select lexems that
// * are associated with definitions from DEX '98 or DEX '09
// * have the shortest total definition length (from all sources)
$lexems = Model::factory('Lexem')
  ->table_alias('l')
  ->select('l.*')
  ->join('EntryDefinition', 'l.entryId = ed.entryId', 'ed')
  ->join('Definition', 'ed.definitionId = d.id', 'd')
  ->where('l.structStatus', Entry::STRUCT_STATUS_NEW)
  ->where_not_equal('d.status', Definition::ST_DELETED)
  ->group_by('l.id')
  ->having_raw('sum(sourceId in (1, 27)) > 0')
  ->having_raw('sum(length(internalRep)) < 300')
  ->limit(100)
  ->find_many();

// Load the definitions for each lexem
$searchResults = array();
foreach ($lexems as $l) {
  $defs = Definition::loadByEntryId($l->entryId);
  $searchResults[] = SearchResult::mapDefinitionArray($defs);
}

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/structChooseLexem.tpl');

?>
