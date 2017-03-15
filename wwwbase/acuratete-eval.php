<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_EDIT | PRIV_ADMIN);

$projectId = Request::get('projectId');
$saveButton = Request::has('saveButton');
$deleteButton = Request::has('deleteButton');
$recomputeSpeedButton = Request::has('recomputeSpeedButton');
$editProjectButton = Request::has('editProjectButton');
$defId = Request::get('defId');
$errors = Request::get('errors');

$project = AccuracyProject::get_by_id($projectId);

if (!$project) {
  FlashMessage::add('Proiectul nu există.', 'danger');
  util_redirect('index.php');
}

$mine = session_getUserId() == $project->ownerId;

if (!$project->visibleTo(session_getUser())) {
  FlashMessage::add('Nu aveți dreptul să vedeți acest proiect.', 'danger');
  util_redirect('index.php');
}

if ($recomputeSpeedButton) {
  $project->recomputeSpeedData();
  $project->save();
  FlashMessage::add('Am recalculat viteza.', 'success');
  util_redirect("?projectId={$projectId}");
}

if ($deleteButton) {
  if ($project) {
    $project->delete();
  }
  FlashMessage::add('Am șters proiectul.', 'success');
  util_redirect('acuratete');
}

if ($editProjectButton) {
  $project->name = Request::get('name');
  $project->method = Request::get('method');
  $project->visibility = Request::get('visibility');
  if ($project->validate()) {
    $project->save();
    FlashMessage::add('Am actualizat datele.', 'success');
    util_redirect("?projectId={$projectId}");
  }
}

if ($saveButton) {
  $ar = AccuracyRecord::get_by_projectId_definitionId($projectId, $defId);
  if (!$ar) {
    $ar = Model::factory('AccuracyRecord')->create();
    $ar->projectId = $projectId;
    $ar->definitionId = $defId;
  }
  $ar->errors = $errors;
  $ar->save();
  util_redirect("?projectId={$projectId}");
}

if ($defId) {
  $def = Definition::get_by_id($defId);
  $ar = AccuracyRecord::get_by_projectId_definitionId($projectId, $defId);
  $errors = $ar->errors;
} else if ($mine) {
  $def = $project->getDefinition();
  $errors = 0;
} else {
  $def = null;
  $errors = 0;
}

if ($def) {
  $homonyms = Entry::getHomonyms($def->getEntries());
  SmartyWrap::assign('homonyms', $homonyms);
}

$defData = $project->getDefinitionData();
$project->computeAccuracyData();

SmartyWrap::assign('project', $project);
SmartyWrap::assign('mine', $mine);
SmartyWrap::assign('def', $def);
SmartyWrap::assign('errors', $errors);
SmartyWrap::assign('definitionData', $defData);
SmartyWrap::display('acuratete-eval.tpl');

?>
