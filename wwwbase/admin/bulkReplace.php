<?php
require_once("../../phplib/Core.php"); 
ini_set('max_execution_time', '3600');
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

$MAX_AFFECTED = 1000;

$search = Request::getRaw('search');
$replace = Request::getRaw('replace');
$sourceId = Request::get('sourceId');
$saveButton = Request::has('saveButton');

// Escape literal percent signs. Use | as escape character so that constructs like
// \% (which in dexonline notation mean "literal percent sign") are unaffected.
$mysqlSearch = str_replace('%', '|%', $search);
$query = Model::factory('Definition')
       ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
       ->where_raw('(binary internalRep like ? escape "|")', ["%{$mysqlSearch}%"]);
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

$diffs = [];

foreach ($defs as $def) {
  $olddef = unserialize(serialize($def));

  $def->internalRep = str_replace($search, $replace, $def->internalRep);
  $ambiguousMatches = [];
  $def->internalRep = AdminStringUtil::sanitize(
      $def->internalRep, $def->sourceId, $ambiguousMatches);
  $def->htmlRep = AdminStringUtil::htmlize($def->internalRep, $def->sourceId);

  $diffs[] = LDiff::htmlDiff($olddef->internalRep, $def->internalRep);

  // Complete or un-complete the abbreviation review
  if (!count($ambiguousMatches) && $def->abbrevReview == Definition::ABBREV_AMBIGUOUS) {
    $def->abbrevReview = Definition::ABBREV_REVIEW_COMPLETE;
  } else if (count($ambiguousMatches) && $def->abbrevReview == Definition::ABBREV_REVIEW_COMPLETE) {
    $def->abbrevReview = Definition::ABBREV_AMBIGUOUS;
  }

  if ($saveButton) {
    $def->save();
  }
}

if ($saveButton) {
  Log::notice("Replaced [{$search}] with [{$replace}] in source $sourceId");
  Util::redirect("index.php");
}

SmartyWrap::assign('search', $search);
SmartyWrap::assign('replace', $replace);
SmartyWrap::assign('sourceId', $sourceId);
SmartyWrap::assign('modUser', User::getActive());
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('diffs', $diffs);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/bulkReplace.tpl');

?>
