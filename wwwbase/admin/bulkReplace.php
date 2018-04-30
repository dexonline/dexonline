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

$targetName = Constant::BULKREPLACE_TARGETS[$target]['text'];

DebugInfo::init();

$mysqlSearch = strtr($search,
  array_combine(array_keys(Constant::MYSQL_LIKE_ESCAPES),
                array_values(Constant::MYSQL_LIKE_ESCAPES)
    )
  );

/**
 * We need the array of Ids, matching the search criteria, only once.
 *  - big array of Ids will be divided into chunks
 *  - further search is based on array_diff of $remainingIds chunks with $excludedIds,
 *    which outperform queries with LIMIT
 *  - counting of objects: remaining, changed and excluded is based on those arrays;
 */
if (!$saveButton) {
  $query = prepareBaseQuery($target, $mysqlSearch, $sourceId);
  $objResults = $query->select('id')->find_many();

  $remainingIds = Util::objectProperty($objResults, 'id');

  $numCount = count($remainingIds);
  unset($objResults);
  DebugInfo::stopClock('BulkReplace - Count - After search criteria');

  // no records? we should not go any further
  if (!$numCount) {
    FlashMessage::add("Nu există {$targetName} care să conțină: [{$search}]", 'warning');
    Util::redirect('index.php');
  }

  // some records? setting up session variables
  Session::set('numCount', $numCount);
  Session::set('numChanged', 0);
  Session::set('numExcluded', 0);
  Session::set('remainingIds', $remainingIds);
  Session::set('structuredIds', []);
  Session::set('finishedReplace', false);
}

// variables should not be null
$numCount = Session::get('numCount');           // count of all objects at first run of this script
$numChanged = Session::get('numChanged');       // count of changed objects, including structured
$numExcluded = Session::get('numExcluded');     // count of excluded objects, including structured
$remainingIds = Session::get('remainingIds');   // array
$structuredIds = Session::get('structuredIds'); // array of structured objects to be reviewed later

/** Form was submitted, process the records */
if ($saveButton) {
  /** preparing array for the subsequent query */
  $queryIds = processObjectIds($remainingIds, $excludedIds, $numExcluded, $limit);

  if (!empty($queryIds)) {
    /** select only those records that were previewed (and not excluded) */
    $query = prepareBaseQuery($target, $mysqlSearch, $sourceId);
    $objects = $query->where_id_in($queryIds)->find_many();
    DebugInfo::stopClock('BulkReplace - AfterQueryObjects +SaveButton');

    /** Save and log */
    saveObjects($objects, $target, $search, $replace, $numChanged, $structuredIds);
    DebugInfo::stopClock('BulkReplace - AfterSaveObjects +SaveButton');
    Log::notice('Replaced [%s] with [%s] in [%s] objects from table %s '. (!$sourceId ?: 'in source [%s]'),
                $search, $replace, $numChanged, Constant::BULKREPLACE_TARGETS[$target]['model'], $sourceId);
    unset($objects);
  }

  /** Test if we are done.
   *  Only if initial count of objects is equal to the sum of changed and excluded
   */
  if ($numCount == $numChanged + $numExcluded) {
    /** a little housekeeping, preparing for redirect */
    unsetVars([ 'numCount', 'numChanged', 'numExcluded', 'remainingIds' ]);

    $msg = sprintf('%s %s ocurențe [%s] din totalul de %s au fost înlocuite cu [%s]',
                   $numChanged,
                   Str::getAmountPreposition($numChanged),
                   $search,
                   $numCount,
                   $replace);
    FlashMessage::add($msg, 'success');
    if (!empty($structuredIds)) {
      Session::set('finishedReplace', true);
      Util::redirect('bulkReplaceStructured.php'); // case history of changed structured definitions
    } else {
      unsetVars(['structuredIds', 'finishedReplace']); // we don't need them anymore
      Util::redirect('index.php'); // nothing else to do
    }
  }
  $remainingIds = array_splice($remainingIds, $limit);
}

/** First time or have more records?
 *  get the chunck of $limit and prepare the query array */
$queryIds = processObjectIds($remainingIds, '', $numExcluded, $limit);

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

$remaining = $numCount - $numChanged - $numExcluded;
$msg = sprintf('%s %s %s se potrivesc ::',
               $numCount,
               Str::getAmountPreposition($numCount),
               $targetName);
if ($numChanged) {
  $msg .= " {$numChanged} au fost modificate ::";
}
if ($numExcluded) {
  $msg .= " {$numExcluded} au fost excluse ::";
}
$msg .= sprintf(" %s vor fi modificate.",
                ($remaining > $limit) ? "maximum {$limit}" : $remaining);

FlashMessage::add($msg, 'warning');
if (!empty($structuredIds)) {
    FlashMessage::addTemplate('bulkReplacedStructured.tpl', [
      'count' => count($structuredIds),
      'prep' => Str::getAmountPreposition(count($structuredIds)),
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
SmartyWrap::assign('structuredChanged', count($structuredIds));
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
    $d->internalRep = DiffUtil::internalDiff($d->internalRep, $new);
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
 * @param   array   $remainingIds     referential
 * @param   string  $excludedIds      retrieved from the submission form
 * @param   int     $numExcluded      referential, count of excluded objects
 * @param   int     $limit            limit of Ids to be filtered
 * @return  array   $queryIds         list of Ids supposed to be changed by replace operations
 */
function processObjectIds(&$remainingIds, $excludedIds, &$numExcluded, $limit) {

  $excludedIds = filter_var_array(
    preg_split('/,/', $excludedIds, null, PREG_SPLIT_NO_EMPTY), FILTER_SANITIZE_NUMBER_INT);
  $numExcluded += count($excludedIds);
  Session::set('numExcluded', $numExcluded);

  $queryIds = array_diff(array_slice($remainingIds, 0, $limit), $excludedIds);
  Session::set('remainingIds', $remainingIds);

  return $queryIds;
}

/**
 * Saves the objects (definition/meaning)
 *
 * @param model   $objects          model of Definition or Meaning
 * @param int     $target           type of $object 1=Definition, 2=Meaning
 * @param string  $search           string to be replaced
 * @param string  $replace          replaces $search
 * @param int     $numChanged       count of changed $objects
 * @param array   $structuredIds maintains throughout the session the defIds to be reviewed
 */
function saveObjects($objects, $target, $search, $replace, &$numChanged, &$structuredIds){
    foreach ($objects as $obj) {
    if ($target == 1) { // $obj is a definition
      definitionReplace($obj, $search, $replace);
      if ($obj->structured){
        $structuredIds[] = $obj->id;
      }
    } else { // $obj is a meaning
      meaningReplace($obj, $search, $replace);
    }
    $obj->save();
    $numChanged++;
  }
  Session::set('numChanged', $numChanged);
  Session::set('structuredIds', $structuredIds);
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
  $query = Model::factory(Constant::BULKREPLACE_TARGETS[$target]['model'])
           ->where_raw('(binary internalRep like ? escape "|")', ["%{$mysqlSearch}%"]);

  if ($target == 1) { // definitions
    $query->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN]);
    if ($sourceId) {
      $query->where('sourceId', $sourceId);
    }
  }

  return $query;
}
