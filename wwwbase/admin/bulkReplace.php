<?php
$startMemory = memory_get_usage();

require_once('../../phplib/Core.php');
ini_set('max_execution_time', '3600');
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

const TARGET_NAMES = [
  1 => 'definiții',
  2 => 'sensuri',
];

$search = Request::getRaw('search');
$replace = Request::getRaw('replace');
$target = Request::get('target');
$sourceId = Request::get('sourceId');
$lastId = intval(Request::get('lastId')); // id of object for further search
$limit = Request::get('limit'); // max possible number of objects that will be changed
$excludedIds = Request::get('excludedIds'); // array of object IDs excluded from changes
$saveButton = Request::has('saveButton');

$targetName = TARGET_NAMES[$target];

DebugInfo::init();

// Use | to escape MySQL special characters so that constructs and chars like
// \% , _ , | (which in dexonline notation means: "literal percent sign", latex
// convention for subscript, the pipe itself) remains unaffected.
$replaceChars = [
  '%' => '|%',
  '_' => '|_',
  '|' => '||',
];
$mysqlSearch = strtr($search, array_combine(array_keys($replaceChars), array_values($replaceChars)));

if ($target == 1) { // definitions
  $query = Model::factory('Definition')
         ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
         ->where_raw('(binary internalRep like ? escape "|")', ["%{$mysqlSearch}%"]);
  if ($sourceId) {
    $query = $query->where('sourceId', $sourceId);
  }
} else { // meanings
  $query = Model::factory('Meaning')
         ->where_raw('(binary internalRep like ? escape "|")', ["%{$mysqlSearch}%"]);
}

// we need the count only once to speed up subsequent replace
if (!$saveButton) {
  $objCount = $query->count();
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
  Session::set('objStructured', []);
  Session::set('finishedReplace', false);
}

// variables should not be null
$objCount = Session::get('objCount');
$objChanged = Session::get('objChanged');
$objExcluded = Session::get('objExcluded');
$objStructured = Session::get('objStructured');

// preparing the main query object with global parameters
$query = $query
       ->order_by_asc('id')
       ->limit($limit);

if ($saveButton) {
  $querySave = $query->where_gt('id', $lastId); // only those records that were previsualized
  $objects = $querySave->find_many();
  DebugInfo::stopClock('BulkReplace - AfterQuery +SaveButton');

  $excludedIds = filter_var_array(
    preg_split('/,/', $excludedIds, null, PREG_SPLIT_NO_EMPTY),
    FILTER_SANITIZE_NUMBER_INT);
  $objExcluded += count($excludedIds);

  foreach ($objects as $obj) {
    $lastId = $obj->id;                     // $lastId will get the final ID
    if (in_array($obj->id, $excludedIds)) {
      continue;                             // don't process exluded IDs
    }

    if ($target == 1) { // $obj is a definition
      definitionReplace($obj, $search, $replace);
      if ($obj->structured){
        $objStructured[] = $obj->id;
      }
      $obj->deepSave();
    } else { // $obj is a meaning
      meaningReplace($obj, $search, $replace);
      $obj->save();
    }

    $objChanged++;
  }
  DebugInfo::stopClock('BulkReplace - AfterForEach +SaveButton');

  Session::set('objStructured', $objStructured);

  Log::notice('Replaced [%s] objects - [%s] with [%s] in source [%s]',
              $objChanged, $search, $replace, $sourceId);
  if ($objCount - $objChanged - $objExcluded == 0) {
    Session::unsetVar('objCount');
    Session::unsetVar('objChanged');
    Session::unsetVar('objExcluded');

    $msg = sprintf('%s %s ocurențe [%s] din totalul de %s au fost înlocuite cu [%s]',
                   $objChanged,
                   Str::getAmountPreposition($objChanged),
                   $search,
                   $objCount,
                   $replace);
    FlashMessage::add($msg, 'success');
    if (!empty($objStructured)) {
      Session::set('finishedReplace', true);
      Util::redirect('bulkReplaceStructured.php'); // case history of changed structured definitions
    } else {
      Session::unsetVar('objStructured'); // we don't need it anymore
      Session::unsetVar('finishedReplace');
      Util::redirect('index.php'); // nothing else to do
    }
  }
}

Session::set('objChanged', $objChanged);
Session::set('objExcluded', $objExcluded);

// more records? we need another query
$remaining = $objCount - $objChanged - $objExcluded;
if ($remaining) {
  $objects = $query
           ->where_gt('id', $lastId)
           ->find_many();
  DebugInfo::stopClock('BulkReplace - AfterQuery +MoreToReplace');

  if ($target == 1) {
    // objects are SearchResults
    $objects = createDefinitionDiffs($objects, $search, $replace);
  } else {
    // objects are Meanings
    $objects = createMeaningDiffs($objects, $search, $replace);
  }

  $msg = sprintf('%s %s %s se potrivesc ::',
                 $objCount,
                 Str::getAmountPreposition($objCount),
                 $targetName);
  if ($objCount) {
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
}

SmartyWrap::assign('search', $search);
SmartyWrap::assign('replace', $replace);
SmartyWrap::assign('target', $target);
SmartyWrap::assign('targetName', $targetName);
SmartyWrap::assign('sourceId', $sourceId);
SmartyWrap::assign('lastId', $lastId);
SmartyWrap::assign('limit', $limit);
SmartyWrap::assign('remaining', $remaining);
SmartyWrap::assign('de', Str::getAmountPreposition(count($objects)));
SmartyWrap::assign('modUser', User::getActive());
SmartyWrap::assign('objects', $objects);
SmartyWrap::assign('structuredChanged', count($objStructured));
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
