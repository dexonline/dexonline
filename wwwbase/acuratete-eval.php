<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_ADMIN);

$projectId = util_getRequestParameter('projectId');
$submitButton = util_getRequestParameter('submitButton');
$deleteButton = util_getRequestParameter('deleteButton');
$defId = util_getRequestParameter('defId');
$errors = util_getRequestParameter('errors');

if ($deleteButton) {
  $ap = AccuracyProject::get_by_id($projectId);
  if ($ap) {
    $ap->delete();
  }
  FlashMessage::add('Am șters proiectul.', 'success');
  util_redirect('acuratete');
}

if ($submitButton) {
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
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::display('acuratete-eval.tpl');

?>
