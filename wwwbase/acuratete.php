<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_ADMIN);

$submitButton = util_getRequestParameter('submitButton');
$id = util_getRequestParameter('id');

$user = session_getUser();

$p = Model::factory('AccuracyProject')->create(); // new project
$p->ownerId = $user->id;

if ($submitButton) {
  $p->name = util_getRequestParameter('name');
  $p->userId = util_getRequestParameter('userId');
  $p->sourceId = util_getRequestParameter('sourceId');
  $p->startDate = util_getRequestParameter('startDate');
  $p->endDate = util_getRequestParameter('endDate');
  $p->method = util_getRequestParameter('method');

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
SmartyWrap::addCss('select2', 'admin');
SmartyWrap::addJs('select2');
SmartyWrap::display('acuratete.tpl');

?>
