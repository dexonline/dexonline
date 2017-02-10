<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_ADMIN);

$projectId = Request::get('projectId');
$saveButton = Request::has('saveButton');
$deleteButton = Request::has('deleteButton');
$recomputeSpeedButton = Request::has('recomputeSpeedButton');
$defId = Request::get('defId');
$errors = Request::get('errors');

$project = AccuracyProject::get_by_id($projectId);

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
} else {
  $def = $project->getDefinition();
  $errors = 0;
}

$defData = $project->getDefinitionData();
$project->computeAccuracyData();

SmartyWrap::assign('project', $project);
SmartyWrap::assign('def', $def);
SmartyWrap::assign('errors', $errors);
SmartyWrap::assign('definitionData', $defData);
SmartyWrap::display('acuratete-eval.tpl');

?>
