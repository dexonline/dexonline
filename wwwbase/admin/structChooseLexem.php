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
  ->join('LexemDefinitionMap', 'l.id = ldm.lexemId', 'ldm')
  ->join('Definition', 'ldm.definitionId = d.id', 'd')
  ->where('l.structStatus', Lexem::STRUCT_STATUS_NEW)
  ->where_not_equal('d.status', ST_DELETED)
  ->group_by('l.id')
  ->having_raw('sum(sourceId in (1, 27)) > 0')
  ->having_raw('sum(length(internalRep)) < 300')
  ->limit(100)
  ->find_many();

// Load the definitions for each lexem
$searchResults = array();
foreach ($lexems as $l) {
  $defs = Definition::loadByLexemId($l->id);
  $searchResults[] = SearchResult::mapDefinitionArray($defs);
}

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('sectionTitle', 'Lexeme ușor de structurat');
SmartyWrap::assign('sectionCount', count($lexems));
//SmartyWrap::assign('allStatuses', util_getAllStatuses());
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/structChooseLexem.tpl');

?>
