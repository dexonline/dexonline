<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_ADMIN);
util_assertNotMirror();

$MAX_AFFECTED = 1000;

$search = util_getRequestParameter('search');
$replace = util_getRequestParameter('replace');
$sourceId = util_getRequestParameter('sourceId');
$saveButton = util_getBoolean('saveButton');

$query = Model::factory('Definition')
       ->where('status', Definition::ST_ACTIVE)
       ->where_raw('(binary internalRep like ?)', ["%{$search}%"]);
if ($sourceId) {
  $query = $query->where('sourceId', $sourceId);
}
$count = $query->count();

if ($count > $MAX_AFFECTED) {
  FlashMessage::add(
    $saveButton
    ? "{$count} definiții se potrivesc, numai {$MAX_AFFECTED} au fost modificate."
    : "{$count} definiții se potrivesc, maxim {$MAX_AFFECTED} vor fi modificate.",
    'warning');
}

$defs = $query
  ->order_by_asc('id')
  ->limit($MAX_AFFECTED)
  ->find_many();

$searchResults = SearchResult::mapDefinitionArray($defs);

foreach ($defs as $def) {
  $def->internalRep = str_replace($search, $replace, $def->internalRep);
  $ambiguousMatches = [];
  $def->internalRep = AdminStringUtil::internalizeDefinition(
    $def->internalRep, $def->sourceId, $ambiguousMatches);
  $def->htmlRep = AdminStringUtil::htmlize($def->internalRep, $def->sourceId);

  // Complete or un-complete the abbreviation review
  if (!count($ambiguousMatches) && $def->abbrevReview == ABBREV_AMBIGUOUS) {
    $def->abbrevReview = ABBREV_REVIEW_COMPLETE;
  } else if (count($ambiguousMatches) && $def->abbrevReview == ABBREV_REVIEW_COMPLETE) {
    $def->abbrevReview = ABBREV_AMBIGUOUS;
  }

  if ($saveButton) {
    $def->save();
  }
}

if ($saveButton) {
  Log::notice("Replaced [{$search}] with [{$replace}] in source $sourceId");
  util_redirect("index.php");
}

SmartyWrap::assign('search', $search);
SmartyWrap::assign('replace', $replace);
SmartyWrap::assign('sourceId', $sourceId);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/bulkReplace.tpl');

?>
