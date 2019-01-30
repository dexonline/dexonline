<?php
require_once '../phplib/Core.php';
User::mustHave(User::PRIV_EDIT | User::PRIV_ADMIN);

$includePublic = Request::has('includePublic');
$submitButton = Request::has('submitButton');
$length = Request::get('length');

$user = User::getActive();

$p = Model::factory('AccuracyProject')->create(); // new project
$p->ownerId = $user->id;

if ($submitButton) {
  $p->name = Request::get('name');
  $p->userId = Request::get('userId');
  $p->sourceId = Request::get('sourceId') ?: 0;
  $p->lexiconPrefix = Request::get('lexiconPrefix');
  $p->startDate = Request::get('startDate') ?: '0000-00-00';
  $p->endDate = Request::get('endDate') ?: '0000-00-00';
  $p->visibility = Request::get('visibility');

  if ($p->validate($length)) {
    if ($length > 0) {
      $p->computeSpeedData();
      $p->save();
      $p->sampleDefinitions($length);
      Util::redirect("acuratete-eval?projectId={$p->id}");
    } else {
      FlashMessage::add('lungimea trebuie să fie > 0');
    }
  }
}

$aps = Model::factory('AccuracyProject');
if ($includePublic && User::can(User::PRIV_ADMIN)) {
  $aps = $aps->where_raw(
    '((ownerId = ?) or (visibility != ?))',
    [ $user->id, AccuracyProject::VIS_PRIVATE ]
  );
} else if ($includePublic && User::can(User::PRIV_EDIT)) {
  $aps = $aps->where_raw(
    '((ownerId = ?) or ((visibility = ?) && (userId = ?)) or (visibility = ?))',
    [ $user->id, AccuracyProject::VIS_EDITOR, $user->id, AccuracyProject::VIS_PUBLIC ]
  );
} else {
  $aps = $aps->where('ownerId', $user->id);
}

$aps = $aps->order_by_asc('name')->find_many();

// build a map of project ID => project
// TODO - do we need this?
$projects = [];
foreach ($aps as $ap) {
  $projects[$ap->id] = $ap;
}

SmartyWrap::assign('projects', $projects);
SmartyWrap::assign('p', $p);
SmartyWrap::assign('length', $length);
SmartyWrap::assign('includePublic', $includePublic);
SmartyWrap::addCss('admin', 'tablesorter');
SmartyWrap::addJs('select2Dev', 'tablesorter');
SmartyWrap::display('acuratete.tpl');
