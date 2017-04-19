<?php
require_once("../phplib/Core.php");

User::require(User::PRIV_EDIT);

$id = Request::get('id');
$def = Definition::get_by_id($id);

$query = 'select h.*, u.nick as unick, mu.nick as munick, s.shortName ' .
       'from history_Definition h ' .
       'left join User u on h.UserId = u.id ' .
       'left join User mu on h.ModUserId = mu.id ' .
       'left join Source s on h.SourceId = s.id ' .
       "where h.Id = $id " .
       'order by Version';
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
  $query = 'select d.userId as UserId, ' .
         'd.sourceId as SourceId, ' .
         'd.status as Status, ' .
         'd.lexicon as Lexicon, ' .
         'd.internalRep as InternalRep, ' .
         'd.modDate as NewDate, ' .
         'u.nick as unick, ' .
         'mu.nick as munick, ' .
         's.shortName as shortName ' .
         'from Definition d ' .
         'left join User u on d.userId = u.id ' .
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

  if($old['UserId'] !== $new['UserId']) {
    $numChanges++;
  }

  if ($old['SourceId'] !== $new['SourceId']) {
    $numChanges++;
  }

  if ($old['Status'] !== $new['Status']) {
    $statuses = Definition::$STATUS_NAMES;
    $changeSet['OldStatusName'] = $statuses[$old['Status']];
    $changeSet['NewStatusName'] = $statuses[$new['Status']];
    $numChanges++;
  }

  if ($old['Lexicon'] !== $new['Lexicon']) {
    $numChanges++;
  }

  if ($old['InternalRep'] !== $new['InternalRep']) {
    $changeSet['diff'] = LDiff::htmlDiff($old['InternalRep'], $new['InternalRep']);
    $numChanges++;
  }

  if ($numChanges) {
    $changeSet['old'] = $old;
    $changeSet['new'] = $new;
    $changeSets[] = $changeSet;
  }
}
