<?php
require_once("../phplib/Core.php");
User::mustHave(User::PRIV_EDIT | User::PRIV_ADMIN);

$includePublic = Request::has('includePublic');
$submitButton = Request::has('submitButton');
$id = Request::get('id');

$user = User::getActive();

$p = Model::factory('AccuracyProject')->create(); // new project
$p->ownerId = $user->id;

if ($submitButton) {
  $p->name = Request::get('name');
  $p->userId = Request::get('userId');
  $p->sourceId = Request::get('sourceId');
  $p->lexiconPrefix = Request::get('lexiconPrefix');
  $p->startDate = Request::get('startDate');
  $p->endDate = Request::get('endDate');
  $p->method = Request::get('method');
  $p->step = Request::get('step');
  $p->visibility = Request::get('visibility');

  if ($p->validate()) {
    $p->recomputeSpeedData();
    $p->save();
    Util::redirect("acuratete-eval?projectId={$p->id}");
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
$projects = [];
foreach ($aps as $ap) {
  $ap->computeAccuracyData();
  $projects[$ap->id] = $ap;
}

SmartyWrap::assign('projects', $projects);
SmartyWrap::assign('p', $p);
SmartyWrap::assign('includePublic', $includePublic);
SmartyWrap::addCss('admin', 'tablesorter');
SmartyWrap::addJs('select2Dev', 'tablesorter');
SmartyWrap::display('acuratete.tpl');
