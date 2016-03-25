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
$count = Model::factory('Definition')->where_raw("status = 0 {$sourceClause} and binary internalRep like '%{$search}%'")->count();
if ($count > $MAX_AFFECTED) {
  if ($realRun) {
    FlashMessage::add("{$count} definiții se potrivesc, numai {$MAX_AFFECTED} au fost modificate.", 'warning');
  } else {
    FlashMessage::add("{$count} definiții se potrivesc, maxim {$MAX_AFFECTED} vor fi modificate.", 'warning');
  }
}

$defs = Model::factory('Definition')->where_raw("status = 0 {$sourceClause} and binary internalRep like '%{$search}%'")->order_by_asc('id')
  ->limit($MAX_AFFECTED)->find_many();
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

SmartyWrap::assign('search', $search);
SmartyWrap::assign('replace', $replace);
SmartyWrap::assign('sourceId', $sourceId);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/bulkReplace.tpl');

?>
