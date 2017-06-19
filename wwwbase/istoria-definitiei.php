<?php
require_once("../phplib/Core.php");

User::mustHave(User::PRIV_EDIT);

$id = Request::get('id');
$def = Definition::get_by_id($id);

$query = 'select dv.*, mu.nick as munick, s.shortName ' .
       'from DefinitionVersion dv ' .
       'left join User mu on dv.modUserId = mu.id ' .
       'left join Source s on dv.sourceId = s.id ' .
       "where dv.definitionId = $id " .
       'order by id';
$recordSet = DB::execute($query);

$prev = null;
$changeSets = [];

foreach ($recordSet as $row) {
  if ($prev) {
    compareVersions($prev, $row, $changeSets);
  }
  $prev = $row;
}

// And once more for the current version
if ($prev) {
  $query = 'select d.sourceId as sourceId, ' .
         'd.status as status, ' .
         'd.lexicon as lexicon, ' .
         'd.internalRep as internalRep, ' .
         'd.modDate as createDate, ' .
         'mu.nick as munick, ' .
         's.shortName as shortName ' .
         'from Definition d ' .
         'left join User mu on d.modUserId = mu.id ' .
         'left join Source s on d.sourceId = s.id ' .
         "where d.id = $id ";
  $recordSet = DB::execute($query);

  foreach ($recordSet as $row) { // just once, really
    compareVersions($prev, $row, $changeSets);
  }
}

$changeSets = array_reverse($changeSets); // newest changes first

SmartyWrap::assign('def', $def);
SmartyWrap::assign('changeSets', $changeSets);
SmartyWrap::display('istoria-definitiei.tpl');

/*************************************************************************/

function compareVersions(&$old, &$new, &$changeSets) {
  $changeSet = [];
  $numChanges = 0;

  if ($old['sourceId'] !== $new['sourceId']) {
    $numChanges++;
  }

  if ($old['status'] !== $new['status']) {
    $statuses = Definition::$STATUS_NAMES;
    $changeSet['oldStatusName'] = $statuses[$old['status']];
    $changeSet['newStatusName'] = $statuses[$new['status']];
    $numChanges++;
  }

  if ($old['lexicon'] !== $new['lexicon']) {
    $numChanges++;
  }

  if ($old['internalRep'] !== $new['internalRep']) {
    $changeSet['diff'] = LDiff::htmlDiff($old['internalRep'], $new['internalRep']);
    $numChanges++;
  }

  if ($numChanges) {
    $changeSet['old'] = $old;
    $changeSet['new'] = $new;
    $changeSets[] = $changeSet;
  }
}
