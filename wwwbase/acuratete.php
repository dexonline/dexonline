<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_ADMIN);

$submitButton = Request::get('submitButton');
$id = Request::get('id');

$user = session_getUser();

$p = Model::factory('AccuracyProject')->create(); // new project
$p->ownerId = $user->id;

if ($submitButton) {
  $p->name = Request::get('name');
  $p->userId = Request::get('userId');
  $p->sourceId = Request::get('sourceId');
  $p->startDate = Request::get('startDate');
  $p->endDate = Request::get('endDate');
  $p->method = Request::get('method');

  if ($p->validate()) {
    $p->save();
    util_redirect("acuratete-eval?projectId={$p->id}");
  }
}

$aps = Model::factory('AccuracyProject')
  ->where('ownerId', $user->id)
  ->order_by_asc('name')
  ->find_many();

// build a map of project ID => project
$projects = [];
foreach ($aps as $ap) {
  $projects[$ap->id] = $ap;
}

SmartyWrap::assign('projects', $projects);
SmartyWrap::assign('p', $p);
SmartyWrap::addCss('admin');
SmartyWrap::addJs('select2Dev');
SmartyWrap::display('acuratete.tpl');

?>
