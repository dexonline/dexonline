<?php
$startMemory = memory_get_usage();

require_once("../../phplib/Core.php"); 
ini_set('max_execution_time', '3600');
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

$search = Request::getRaw('search');
$replace = Request::getRaw('replace');
$sourceId = Request::get('sourceId');
$engine = Request::get('engine');
$granularity = Request::get('granularity');
$lastId = intval(Request::get('lastId')); // id of definition for further search
$maxaffected = Request::get('maxaffected'); // max possible number of definitions that will be changed
$excludedIds = Request::get('excludedIds'); // array of definition ids excluded from changes
$saveButton = Request::has('saveButton');

if (DebugInfo::isEnabled()){  DebugInfo::init(); }

// Escape literal percent signs. Use | as escape character so that constructs like
// \% (which in dexonline notation mean "literal percent sign") are unaffected.
$mysqlSearch = str_replace('%', '|%', $search);
$query = Model::factory('Definition')
       ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
       ->where_raw('(binary internalRep like ? escape "|")', ["%{$mysqlSearch}%"]);
if ($sourceId) {
  $query = $query->where('sourceId', $sourceId);
}

// we need the count only one time to speed up subsequent replace
if (!$saveButton) {
    $totalDefs = $query->count();
    DebugInfo::stopClock("BulkReplace - Count - After search criteria");

    // no records? we should not go any further
    if (!$totalDefs) {
      FlashMessage::add("Nu există nicio definiție care să conțină: [".$search."].", 'warning');
      Util::redirect("index.php");
    }
    
    // some records? setting up session variables
    Session::set('totalDefs', $totalDefs);
    Session::set('changedDefs', 0);
    Session::set('excludedDefs', 0);
}

// variables should not be null
$totalDefs = Session::get('totalDefs');
$changedDefs = Session::get('changedDefs');
$excludedDefs = Session::get('excludedDefs');

// preparing the main query object with global parameters
$query = $query
  ->order_by_asc('id')
  ->limit($maxaffected);

if ($saveButton) {
  $querySave = $query->where_gt('id', $lastId); // only those records that were previsualized
  $defs = $querySave->find_many();
  DebugInfo::stopClock("BulkReplace - AfterQuery +SaveButton");
  
  $excludedIds = filter_var_array(preg_split('/,/', $excludedIds, null, PREG_SPLIT_NO_EMPTY), FILTER_SANITIZE_NUMBER_INT);
  $excludedDefs += count($excludedIds);
  
  foreach ($defs as $def) {
    $lastId = $def->id;                     // $lastId will get the final defId
    if (in_array($def->id, $excludedIds)) {
      continue;                             // don't process exluded Ids
    }
    $def->internalRep = str_replace($search, $replace, $def->internalRep);
    $ambiguousMatches = [];
    $def->internalRep = AdminStringUtil::sanitize(
      $def->internalRep, $def->sourceId, $ambiguousMatches);
    
    // Complete or un-complete the abbreviation review
    if (!count($ambiguousMatches) && $def->abbrevReview == Definition::ABBREV_AMBIGUOUS) {
      $def->abbrevReview = Definition::ABBREV_REVIEW_COMPLETE;
    } else if (count($ambiguousMatches) && $def->abbrevReview == Definition::ABBREV_REVIEW_COMPLETE) {
      $def->abbrevReview = Definition::ABBREV_AMBIGUOUS;
    }    
    $def->htmlRep = AdminStringUtil::htmlize($def->internalRep, $def->sourceId);
    $def->save();
    $changedDefs++;
  }
  DebugInfo::stopClock("BulkReplace - AfterForEach +SaveButton");
    
  Log::notice("Replaced [".$changedDefs."] definitions - [{$search}] with [{$replace}] in source [$sourceId]");
  if ($totalDefs - $changedDefs - $excludedDefs == 0) { 
      Session::unsetVar('totalDefs');
      Session::unsetVar('changedDefs');
      Session::unsetVar('excludedDefs');
      FlashMessage::add("".$totalDefs.StringUtil::getAmountPreposition($totalDefs)." ocurențe: [".$search."] au fost înlocuite cu [".$replace."].", 'success');
      Util::redirect("index.php"); 
  }
}

Session::set('changedDefs', $changedDefs);
Session::set('excludedDefs', $excludedDefs);

// more records? we need another query
if ($totalDefs > $changedDefs) {
  $queryRemain = $query->where_gt('id', $lastId);
  $defs = $queryRemain->find_many();
  DebugInfo::stopClock("BulkReplace - AfterQuery +MoreToReplace");
  
  $searchResults = SearchResult::mapDefinitionArray($defs);

  DebugInfo::stopClock("BulkReplace - AfterMapDefinition");
  
  // speeding up the display 
  foreach ($defs as $def) {
    // we temporary store the replaced internalRep
    $def->htmlRep = str_replace($search, $replace, $def->internalRep);

    // getting the diff from $old (internalRep) -> $new (htmlRep)
    if ($engine == DiffUtil::DIFF_ENGINE_FINEDIFF) {
        $opcodes = FineDiff::getDiffOpcodes($def->internalRep, $def->htmlRep, $granularity);
        $diff = FineDiff::renderDiffToHTMLFromOpcodes($def->internalRep, $opcodes, null, false);
    } else if ($engine == DiffUtil::DIFF_ENGINE_LDIFF) {
        // granularity is taken from Session preferences variable $SplitLevel, so we do not pass it
        $diff = LDiff::htmlDiff($def->internalRep, $def->htmlRep); 
    } else {
        //other engines
    }
    // replacing with the diff only for viewing purposes
    $def->htmlRep = AdminStringUtil::htmlize($diff, $def->sourceId);
  }
  DebugInfo::stopClock("BulkReplace - AfterForEach +MoreToReplace");

  $msgTotalDef = $totalDefs.StringUtil::getAmountPreposition($totalDefs)." definiții se potrivesc ::";
  $msgChangedDef = $changedDefs ? " ".$changedDefs." au fost modificate ::" : "";
  $msgExcludedDef = $excludedDefs ? " ".$excludedDefs." au fost excluse ::" : "";
  $msgWillChangeDef = " ".($totalDefs - $changedDefs > $maxaffected ? " maximum {$maxaffected}" : $totalDefs - $changedDefs);
  $msgWillChangeDef .= " vor fi modificate.";

  FlashMessage::add($msgTotalDef.$msgChangedDef.$msgExcludedDef.$msgWillChangeDef, 'warning');
}

SmartyWrap::assign('search', $search);
SmartyWrap::assign('replace', $replace);
SmartyWrap::assign('sourceId', $sourceId);
SmartyWrap::assign('engine', $engine);
SmartyWrap::assign('granularity', $granularity);
SmartyWrap::assign('lastId', $lastId);
SmartyWrap::assign('maxaffected', $maxaffected);
SmartyWrap::assign('remainedDefs', $totalDefs - $changedDefs - $excludedDefs);
SmartyWrap::assign('de', StringUtil::getAmountPreposition(count($searchResults)));
SmartyWrap::assign('modUser', User::getActive());
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::addJs('diff');
SmartyWrap::addCss('admin', 'diff');
SmartyWrap::display('admin/bulkReplace.tpl');

Log::notice((memory_get_usage() - $startMemory)." bytes used");

?>
