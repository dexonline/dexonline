<?php
$startMemory = memory_get_usage();

require_once('../../phplib/Core.php');
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

$objStructuredIds = Session::get('objStructuredIds');
$finishedReplace = Session::get('finishedReplace');

if ($objStructuredIds == null) {
    $msg = 'Nu am primit niciun parametru pentru a putea afișa lista definițiilor structurate modificate.';
    FlashMessage::add($msg, 'danger');
    Util::redirect('index.php'); // nothing else to do
}

DebugInfo::init();

$defs = Model::factory('Definition')
       ->where_in('id', $objStructuredIds)
       ->order_by_asc('id')
       ->find_many();

$defResults = createDefinitionDiffs($defs);

$defIds = implode(',', $objStructuredIds);

if ($finishedReplace){
  Session::unsetVar('finishedReplace');
  Session::unsetVar('objStructured');
}

// we do not want to overcrowd final list, so filtering only possible modified entries
$entries = Model::factory('Entry')
          ->table_alias('e')
          ->select('e.*')
          ->select('ed.definitionId')
          ->join('EntryDefinition', ['e.id', '=', 'ed.entryId'], 'ed')
          ->where_in('ed.definitionId', $objStructuredIds)
          ->where_in('e.structStatus', [Entry::STRUCT_STATUS_IN_PROGRESS, Entry::STRUCT_STATUS_DONE])
          ->order_by_asc('ed.definitionId')
          ->order_by_asc('ed.entryRank')  // ca intrările să apară în ordinea în care le-a aranjat editorul
          ->find_many();
        
$entryResults = [];
foreach ($entries as $e) {
  // câmpul definitionId nu este nativ pe Entry, ci este extras de query-ul de mai sus
  $entryResults[$e->definitionId][] = $e;
}

DebugInfo::stopClock('BulkReplaceStructured - AfterEntryResults');

SmartyWrap::assign('modUser', User::getActive());
SmartyWrap::assign('defResults', $defResults);
SmartyWrap::assign('entryResults', $entryResults);
SmartyWrap::assign('finished', $finishedReplace);
SmartyWrap::addCss('admin', 'diff');
SmartyWrap::display('admin/bulkReplaceStructured.tpl');

Log::notice((memory_get_usage() - $startMemory).' bytes used');

/*************************************************************************/

function createDefinitionDiffs($defs) {
  $searchResults = SearchResult::mapDefinitionArray($defs);
  DebugInfo::stopClock('BulkReplaceStructured - AfterMapDefinition');
  
  foreach ($defs as $d) {
    $dv = Model::factory('DefinitionVersion')
     ->where('definitionId', $d->id)
     ->order_by_desc('id')
     ->find_one();

    // getting the diff from $old ($dv->internalRep) -> $new($d->internalRep)
    if ($dv != null){
    $diffDef = DiffUtil::internalDiff($dv->internalRep, $d->internalRep);
    list($d->htmlRep, $ignored) = Str::htmlize($diffDef, $d->sourceId);
    }
  }
  DebugInfo::stopClock('BulkReplaceStructured - AfterCreateDefinitionDiffs');

  return $searchResults;
}