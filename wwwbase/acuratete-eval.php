<?php
require_once("../phplib/util.php");
User::require(User::PRIV_EDIT | User::PRIV_ADMIN);

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
  Util::redirect('index.php');
}

$mine = Session::getUserId() == $project->ownerId;

if (!$project->visibleTo(Session::getUser())) {
  FlashMessage::add('Nu aveți dreptul să vedeți acest proiect.', 'danger');
  Util::redirect('index.php');
}

if ($recomputeSpeedButton) {
  $project->recomputeSpeedData();
  $project->save();
  FlashMessage::add('Am recalculat viteza.', 'success');
  Util::redirect("?projectId={$projectId}");
}

if ($deleteButton) {
  if ($project) {
    $project->delete();
  }
  FlashMessage::add('Am șters proiectul.', 'success');
  Util::redirect('acuratete');
}

if ($editProjectButton) {
  $project->name = Request::get('name');
  $project->method = Request::get('method');
  $project->visibility = Request::get('visibility');
  if ($project->validate()) {
    $project->save();
    FlashMessage::add('Am actualizat datele.', 'success');
    Util::redirect("?projectId={$projectId}");
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
  Util::redirect("?projectId={$projectId}");
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
