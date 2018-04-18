<?php
$startMemory = memory_get_usage();

require_once('../../phplib/Core.php');
ini_set('max_execution_time', '3600');
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

$search = Request::getRaw('search');
$replace = Request::getRaw('replace');
$target = Request::get('target');
$sourceId = Request::get('sourceId');
$limit = Request::get('limit'); // max possible number of objects that will be changed
$saveButton = Request::has('saveButton');

$excludedIds = Request::get('excludedIds'); // array of object IDs excluded from changes

$targetName = Constant::TARGET_NAMES[$target]['select'];

DebugInfo::init();

$mysqlSearch = strtr($search, 
  array_combine(array_keys(Constant::BULKREPLACE_ESCAPES), 
                array_values(Constant::BULKREPLACE_ESCAPES)
    )
  );

/** 
 * We need the array of Ids, matching the search criteria, only once.
 *  - big array of Ids will be divided into chunks
 *  - further search is based on array_diff of $objRemainingIds chunks with $excludedIds, 
 *    which outperform queries with LIMIT
 *  - counting of objects: remaining, changed and excluded is based on those arrays
 */
if (!$saveButton) {
  $query = prepareBaseQuery($target, $mysqlSearch, $sourceId);
  $objResults = $query->select('id')->find_many();
  
  foreach ($objResults as $o) {
    $objRemainingIds[] = $o->id;
  }
  $objCount = count($objRemainingIds);
  $objRemainingIds = array_chunk($objRemainingIds, $limit);
  unset($objResults);
  DebugInfo::stopClock('BulkReplace - Count - After search criteria');

  // no records? we should not go any further
  if (!$objCount) {
    FlashMessage::add("Nu există {$targetName} care să conțină: [{$search}]", 'warning');
    Util::redirect('index.php');
  }

  // some records? setting up session variables
  Session::set('objCount', $objCount);
  Session::set('objChanged', 0);
  Session::set('objExcluded', 0);
  Session::set('objRemainingIds', $objRemainingIds);
  Session::set('objStructuredIds', []);
  Session::set('phase', 0);
  Session::set('finishedReplace', false);
}

// variables should not be null
$phase = Session::get('phase');
$objCount = Session::get('objCount');
$objChanged = Session::get('objChanged');
$objExcluded = Session::get('objExcluded');
$objRemainingIds = Session::get('objRemainingIds');
$objStructuredIds = Session::get('objStructuredIds');

/** Form was submitted, process the records */
if ($saveButton) {
  /** preparing array for the subsequent query */
  $queryIds = processObjectIds($objRemainingIds, $excludedIds, $objExcluded, $phase);
  
  if (!empty($queryIds)) {
    /** select only those records that were previewed (and not excluded) */
    $query = prepareBaseQuery($target, $mysqlSearch, $sourceId);
    $objects = $query->where_id_in($queryIds)->find_many();
    DebugInfo::stopClock('BulkReplace - AfterQueryObjects +SaveButton');

    /** Save and log */
    saveObjects($objects, $target, $search, $replace, $objChanged, $objStructuredIds);
    DebugInfo::stopClock('BulkReplace - AfterSaveObjects +SaveButton');
    Log::notice('Replaced [%s] with [%s] in [%s] objects from table %s '. ($sourceId ?: ' in source [%s]'),
                $search, $replace, $objChanged, Constant::TARGET_NAMES[$target]['model'], $sourceId);
    unset($objects);
  }
  
  /** Test if we are done */
  if ($objCount == $objChanged + $objExcluded) {
    /** a little housekeeping, preparing for redirect */
    unsetVars([ 'phase', 'objCount', 'objChanged', 'objExcluded', 'objRemainingIds' ]);

    $msg = sprintf('%s %s ocurențe [%s] din totalul de %s au fost înlocuite cu [%s]',
                   $objChanged,
                   Str::getAmountPreposition($objChanged),
                   $search,
                   $objCount,
                   $replace);
    FlashMessage::add($msg, 'success');
    if (!empty($objStructuredIds)) {
      Session::set('finishedReplace', true);
      Util::redirect('bulkReplaceStructured.php'); // case history of changed structured definitions
    } else {
      unsetVars(['objStructured', 'finishedReplace']); // we don't need them anymore
      Util::redirect('index.php'); // nothing else to do
    }
  }
  unset($objRemainingIds[$phase]);
  $phase++;
}

/** First time or have more records?
 *  get the chunck of $phase and prepare the query array */
$queryIds = processObjectIds($objRemainingIds, '', $objExcluded, $phase);

/** select $limit records */
$query = prepareBaseQuery($target, $mysqlSearch, $sourceId);
$objects = $query->where_id_in($queryIds)->find_many();
DebugInfo::stopClock('BulkReplace - AfterQuery +MoreToReplace');

/** Diffing the returned objects */
if ($target == 1) {
  // objects are SearchResults
  $objects = createDefinitionDiffs($objects, $search, $replace);
} else {
  // objects are Meanings
  $objects = createMeaningDiffs($objects, $search, $replace);
}

$remaining = $objCount - $objChanged - $objExcluded;
$msg = sprintf('%s %s %s se potrivesc ::',
               $objCount,
               Str::getAmountPreposition($objCount),
               $targetName);
if ($objChanged) {
  $msg .= " {$objChanged} au fost modificate ::";
}
if ($objExcluded) {
  $msg .= " {$objExcluded} au fost excluse ::";
}
$msg .= sprintf(" %s vor fi modificate.",
                ($remaining > $limit) ? "maximum {$limit}" : $remaining);

FlashMessage::add($msg, 'warning');
if (!empty($objStructured)) {
    FlashMessage::addTemplate('bulkReplacedStructured.tpl', [
      'count' => count($objStructured),
      'prep' => Str::getAmountPreposition(count($objStructured)),
    ]);
}

/** Finally displaying the template*/
SmartyWrap::assign('search', $search);
SmartyWrap::assign('replace', $replace);
SmartyWrap::assign('target', $target);
SmartyWrap::assign('targetName', $targetName);
SmartyWrap::assign('sourceId', $sourceId);
SmartyWrap::assign('limit', $limit);
SmartyWrap::assign('remaining', $remaining);
SmartyWrap::assign('de', Str::getAmountPreposition(count($objects)));
SmartyWrap::assign('modUser', User::getActive());
SmartyWrap::assign('objects', $objects);
SmartyWrap::assign('structuredChanged', count($objStructuredIds));
SmartyWrap::addJs('diff');
SmartyWrap::addCss('admin', 'diff');
SmartyWrap::display('admin/bulkReplace.tpl');

Log::notice((memory_get_usage() - $startMemory).' bytes used');

/*************************************************************************/

function definitionReplace($d, $search, $replace) {
  $d->internalRep = str_replace($search, $replace, $d->internalRep);
  $d->process(false);
}

function meaningReplace($m, $search, $replace) {
  $m->internalRep = str_replace($search, $replace, $m->internalRep);
  $m->process(false);
}

function createDefinitionDiffs($defs, $search, $replace) {
  $searchResults = SearchResult::mapDefinitionArray($defs);
  DebugInfo::stopClock('BulkReplace - AfterMapDefinition');

  foreach ($defs as $d) {
    // we temporary store the replaced internalRep
    $new = str_replace($search, $replace, $d->internalRep);

    // getting the diff from $old (internalRep) -> $new
    $diff = DiffUtil::internalDiff($d->internalRep, $new);
    list($d->htmlRep, $ignored) = Str::htmlize($diff, $d->sourceId);
  }
  DebugInfo::stopClock('BulkReplace - AfterForEach +MoreToReplace');

  return $searchResults;
}

function createMeaningDiffs($meanings, $search, $replace) {
  foreach ($meanings as $m) {
    $new = str_replace($search, $replace, $m->internalRep);
    $diff = DiffUtil::internalDiff($m->internalRep, $new);
    list($m->htmlRep, $ignored) = Str::htmlize($diff, 0);
  }
  DebugInfo::stopClock('BulkReplace - created meaning diffs');

  return $meanings;
}

/**
 * Process the session arrays and replaces them
 * 
 * @param   array   $objRemainingIds  referential
 * @param   string  $excludedIds      retrieved from the submission form
 * @param   int     $objExcluded      referential, count of excluded objects
 * @param   int     $phase            keeps track of $objRemainingIds chunks
 * @return  array   $queryIds         list of Ids supposed to be changed by replace operations
 */
function processObjectIds(&$objRemainingIds, $excludedIds, &$objExcluded, $phase) {
 
  $objExcludedIds = filter_var_array(
    preg_split('/,/', $excludedIds, null, PREG_SPLIT_NO_EMPTY), FILTER_SANITIZE_NUMBER_INT);
  $objExcluded += count($objExcludedIds);
  Session::set('objExcluded', $objExcluded);
  
  $queryIds = array_diff($objRemainingIds[$phase], $objExcludedIds);
  Session::set('phase', $phase);
  Session::set('objRemainingIds', $objRemainingIds);
 
  return $queryIds;
}

/**
 * Saves the objects (definition/meaning)
 * 
 * @param model   $objects          model of Definition or Meaning
 * @param int     $target           type of $object 1=Definition, 2=Meaning
 * @param string  $search           string to be replaced
 * @param string  $replace          replaces $search
 * @param int     $objChanged       count of changed $objects
 * @param array   $objStructuredIds maintains throughout the session the defIds to be reviewed
 */
function saveObjects($objects, $target, $search, $replace, &$objChanged, &$objStructuredIds){
    foreach ($objects as $obj) {
    if ($target == 1) { // $obj is a definition
      definitionReplace($obj, $search, $replace);
      if ($obj->structured){
        $objStructuredIds[] = $obj->id;
      }
      $obj->deepSave();
    } else { // $obj is a meaning
      meaningReplace($obj, $search, $replace);
      $obj->save();
    }
    $objChanged++;
  }
  Session::set('objChanged', $objChanged);
  Session::set('objStructuredIds', $objStructuredIds);
}

/**
 * 
 * @param array $var
 */
function unsetVars($var){
  foreach ($var as $value) {
    Session::unsetVar($value);
  }
}

/**
 * Prepares the base query, according to some values
 * 
 * @param   int         $target       1-Definition, 2-Meaning
 * @param   string      $mysqlSearch  search string
 * @param   int         $sourceId     used only for <i>$target</i> 1-Definition
 * @return  ORMWrapper                based on <b>$target</b>
 */
function prepareBaseQuery($target, $mysqlSearch, $sourceId){
  $query = Model::factory(Constant::TARGET_NAMES[$target]['model'])
           ->where_raw('(binary internalRep like ? escape "|")', ["%{$mysqlSearch}%"]);
  
  if ($target == 1) { // definitions
    $query->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN]);
    if ($sourceId) {
      $query->where('sourceId', $sourceId);
    }
  }
  
  return $query;
}
