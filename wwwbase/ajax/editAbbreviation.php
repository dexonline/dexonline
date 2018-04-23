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

if (!$abbrevId) {
  $abbrev = Model::factory('Abbreviation')->create();
  $abbrev->sourceId = $sourceId;
} else {
  $abbrev = Abbreviation::get_by_id($abbrevId);
}

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

$response = [ 'id' => $abbrev->id,
              'action' => $action,
              'html' => $html, ];

header('Content-Type: application/json');
print json_encode($response);
