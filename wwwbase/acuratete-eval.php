<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_ADMIN);

$projectId = Request::get('projectId');
$saveButton = Request::has('saveButton');
$deleteButton = Request::has('deleteButton');
$defId = Request::get('defId');
$errors = Request::get('errors');

if ($deleteButton) {
  $ap = AccuracyProject::get_by_id($projectId);
  if ($ap) {
    $ap->delete();
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

$project = AccuracyProject::get_by_id($projectId);

if ($defId) {
  $def = Definition::get_by_id($defId);
  $ar = AccuracyRecord::get_by_projectId_definitionId($projectId, $defId);
  $errors = $ar->errors;
} else {
  $def = $project->getDefinition();
  $errors = 0;
}

$defData = $project->getDefinitionData();
$accData = $project->getAccuracyData();

SmartyWrap::assign('project', $project);
SmartyWrap::assign('def', $def);
SmartyWrap::assign('errors', $errors);
SmartyWrap::assign('definitionData', $defData);
SmartyWrap::assign('accuracyData', $accData);
SmartyWrap::display('acuratete-eval.tpl');

?>
