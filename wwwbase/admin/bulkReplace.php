<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_ADMIN);
util_assertNotMirror();

$MAX_AFFECTED = 1000;

$search = util_getRequestParameter('search');
$replace = util_getRequestParameter('replace');
$sourceId = util_getRequestParameter('source');
$realRun = util_getRequestParameter('realRun');

$sourceClause = $sourceId ? "and sourceId = $sourceId" : '';
$count = db_getSingleValue("select count(*) from Definition where status = 0 {$sourceClause} and binary internalRep like '%{$search}%'");
if ($count > $MAX_AFFECTED) {
  if ($realRun) {
    flash_add("{$count} definiții se potrivesc, numai {$MAX_AFFECTED} au fost modificate.", 'info');
  } else {
    flash_add("{$count} definiții se potrivesc, maxim {$MAX_AFFECTED} vor fi modificate.");
  }
}

$defs = db_find(new Definition(), "status = 0 {$sourceClause} and binary internalRep like '%{$search}%' order by id limit {$MAX_AFFECTED}");
$searchResults = SearchResult::mapDefinitionArray($defs);

foreach ($defs as $def) {
  $def->internalRep = str_replace($search, $replace, $def->internalRep);
  $ambiguousMatches = array();
  $def->internalRep = AdminStringUtil::internalizeDefinition($def->internalRep, $def->sourceId, $ambiguousMatches);
  $def->htmlRep = AdminStringUtil::htmlize($def->internalRep, $def->sourceId);
  // Complete or un-complete the abbreviation review
  if (!count($ambiguousMatches) && $def->abbrevReview == ABBREV_AMBIGUOUS) {
    $def->abbrevReview = ABBREV_REVIEW_COMPLETE;
  } else if (count($ambiguousMatches) && $def->abbrevReview == ABBREV_REVIEW_COMPLETE) {
    $def->abbrevReview = ABBREV_AMBIGUOUS;
  }
  if ($realRun) {
    $def->save();
  }
}

if ($realRun) {
  util_redirect("index.php");
}

smarty_assign('search', $search);
smarty_assign('replace', $replace);
smarty_assign('sourceId', $sourceId);
smarty_assign('searchResults', $searchResults);
smarty_assign('allStatuses', util_getAllStatuses());
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/bulkReplace.ihtml');

?>
