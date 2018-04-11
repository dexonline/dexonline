<?php
$startMemory = memory_get_usage();

require_once('../../phplib/Core.php');
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

const SEP_GRCONCAT ='␣'; // ␣ = &#8203;
const SEP_CONCAT = '|';

$objStructured = Session::get('objStructured');

if ($objStructured == null) {
  //$objStructured = unserialize(rawurldecode(Request::get('objStructured')));
  $objStructured = explode(',', Request::get('objStructured'));
  if ($objStructured == null) {
    $msg = 'Nu am primit niciun parametru pentru a putea afișa lista.';
    FlashMessage::add($msg, 'danger');
    Util::redirect('index.php'); // nothing else to do
  }
}

DebugInfo::init();

$defs = Model::factory('Definition')
       ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
       ->where_in('id', $objStructured)
       ->order_by_asc('id')
       ->find_many();

$defResults = createDefinitionDiffs($defs);

$defIds = implode(',', $objStructured);

$query = 
  "SELECT ed.definitionId, GROUP_CONCAT(CONCAT_WS('" . SEP_CONCAT . "', e.description, e.id, e.structStatus) SEPARATOR '" . SEP_GRCONCAT . "') AS entries " .
  "FROM EntryDefinition ed " .
  "INNER JOIN Entry e ON e.id = ed.entryId " .
  "WHERE ed.definitionId IN (" . $defIds . ") " .
  "GROUP BY definitionId";

$ents = DB::execute($query);
DebugInfo::stopClock('BulkReplaceStructured - AfterQueryEntries');

$entryResults = [];
foreach ($ents as $row) {
  $entries = explode(SEP_GRCONCAT, $row['entries']);
  $variants = [];
  foreach ($entries as $e) {
    $variants[] = explode(SEP_CONCAT, $e);
  }
  
  $entryResults[$row['definitionId']] = $variants; 
}
DebugInfo::stopClock('BulkReplaceStructured - AfterForeachEntries');

SmartyWrap::assign('modUser', User::getActive());
SmartyWrap::assign('defResults', $defResults);
SmartyWrap::assign('entryResults', $entryResults);
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
    $diffDef = DiffUtil::internalDiff($dv->internalRep, $d->internalRep);
    list($d->htmlRep, $ignored) = Str::htmlize($diffDef, $d->sourceId);
  }
  DebugInfo::stopClock('BulkReplaceStructured - AfterForEach +MoreToReplace');

  return $searchResults;
}