<?php

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

$action = Request::get('action');
$abbrevId = Request::get('abbrevId');
$sourceId = Request::get('sourceId');
$short = Request::get('short');
$internalRep = Request::get('internalRep');
$enforced = Request::has('enforced');
$ambiguous = Request::has('ambiguous');
$caseSensitive = Request::has('caseSensitive');
$userId = User::getActiveId();
$status = 'hold';
$html = '';

/** Excluding 'delete' we need to search for a duplicate */
if ($action != 'delete') {
  $abbrev = Abbreviation::getDuplicate($abbrevId, $short, $sourceId);
  if ($abbrev) {
    $action = 'duplicate';
  }
}

if ($abbrevId) {
  $abbrev = Abbreviation::get_by_id($abbrevId);
}
else {
  $abbrev = Model::factory('Abbreviation')->create();
  $abbrev->sourceId = $sourceId;
}

switch ($action) {
  case 'delete':
    $count = Definition::countAbbrevs($short, $sourceId, $caseSensitive);
    if ($count) {
      $html = 'Notația internă #' . $short . '# a fost găsită în ' . $count .
        Str::getAmountPreposition($count) . ' definiții. Reprocesați definițiile!';
      Log::notice('Deleted [%s] abbreviation from source [%s] ', $short, $sourceId);
    }
    else {
      $status = 'finished';
    }
    $abbrev->delete();
    break;

  case 'duplicate':
    $html = 'Această abreviere există!';
    break;

  default:
    /** Populate the fields with new values and save */
    $abbrev->short = $short;
    $abbrev->internalRep = $internalRep;
    list($abbrev->htmlRep, $ignored) = Str::htmlize($internalRep, $sourceId);
    $abbrev->enforced = $enforced;
    $abbrev->ambiguous = $ambiguous;
    $abbrev->caseSensitive = $caseSensitive;
    $abbrev->modUserId = $userId;
    $abbrev->save();

    /** Prepare the tableRow from template */
    SmartyWrap::assign('row', $abbrev);
    SmartyWrap::assign('labelEdited', 'primary');
    $html = SmartyWrap::fetch('bits/abbrevRow.tpl');
    $status = 'finished';
    break;

}

$response = [ 'id' => $abbrev->id,
              'action' => $action,
              'status' => $status,
              'html' => $html, ];

header('Content-Type: application/json');
print json_encode($response);
