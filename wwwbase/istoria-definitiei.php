<?php
require_once("../phplib/util.php");

util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$id = util_getRequestIntParameter('id');
$def = Definition::get_by_id($id);

$recordSet = db_execute("SELECT old.Version AS OldVersion, new.Version AS NewVersion, old.ModDate AS OldDate, new.ModDate AS NewDate, old.UserId AS OldUserId, new.UserId AS NewUserId, oldUser.nick AS OldUserNick, newUser.nick AS NewUserNick, old.Status AS OldStatus, new.Status AS NewStatus, old.SourceId AS OldSourceId, new.SourceId AS NewSourceId, oldSource.shortName AS OldSourceName, newSource.shortName AS NewSourceName, old.Lexicon AS OldLexicon, new.Lexicon as NewLexicon, old.ModUserId AS OldModUserId, new.ModUserId AS NewModUserId, oldModUser.nick AS OldModUserNick, newModUser.nick AS NewModUserNick, old.InternalRep AS OldInternalRep, new.InternalRep AS NewInternalRep FROM history_Definition AS old LEFT JOIN User AS oldUser ON old.UserId = oldUser.id LEFT JOIN User AS oldModUser ON old.ModUserId = oldModUser.id LEFT JOIN Source AS oldSource ON old.SourceId = oldSource.id, history_Definition AS new LEFT JOIN User AS newUser ON new.UserId = newUser.id LEFT JOIN User AS newModUser ON new.ModUserId = newModUser.id LEFT JOIN Source AS newSource ON new.SourceId = newSource.id WHERE old.Id = new.Id AND old.Action = new.Action AND new.Version = old.Version + 1 AND old.NewDate = new.ModDate AND old.Action = 'UPDATE' AND old.Id = '$id' ORDER BY old.Version DESC");

$changeSets = array();
$diffs = array();
$statuses = util_getAllStatuses();

foreach ($recordSet as $row) {
  $changeSet = $row;
  $changeSet['changesCount'] = 0;

  if($row['OldUserId'] !== $row['NewUserId']) {
    $changeSet['changesCount']++;
  }

  if($row['OldSourceId'] !== $row['NewSourceId']) {
    $changeSet['changesCount']++;
  }

  if($row['OldStatus'] !== $row['NewStatus']) {
    $changeSet['OldStatusName'] = $statuses[$row['OldStatus']];
    $changeSet['NewStatusName'] = $statuses[$row['NewStatus']];
    $changeSet['changesCount']++;
  }

  if($row['OldLexicon'] !== $row['NewLexicon']) {
    $changeSet['changesCount']++;
  }

  if($row['OldModUserId'] !== $row['NewModUserId']) {
    $changeSet['changesCount']++;
  }

  if($row['OldInternalRep'] !== $row['NewInternalRep']) {
    $changeSet['diff'] = LDiff::htmlDiff($row['OldInternalRep'], $row['NewInternalRep']);
    $changeSet['changesCount']++;
  }

  if($changeSet['changesCount'] > 0) {
    $changeSets[] = $changeSet;
  }
}

SmartyWrap::assign('page_title', 'Istoria definiției ' . $def->lexicon);
SmartyWrap::assign('def', $def);
SmartyWrap::assign('changeSets', $changeSets);
SmartyWrap::display('istoria-definitiei.ihtml');
