<?php
require_once('../phplib/Core.php');
User::mustHave(User::PRIV_EDIT | User::PRIV_ADMIN);

$projectId = Request::get('projectId');
$saveButton = Request::has('saveButton');
$deleteButton = Request::has('deleteButton');
$editProjectButton = Request::has('editProjectButton');
$defId = Request::get('defId');
$errors = Request::get('errors');

$project = AccuracyProject::get_by_id($projectId);

if (!$project) {
  FlashMessage::add('Proiectul nu există.', 'danger');
  Util::redirect('index.php');
}

$mine = User::getActiveId() == $project->ownerId;

if (!$project->visibleTo(User::getActive())) {
  FlashMessage::add('Nu aveți dreptul să vedeți acest proiect.', 'danger');
  Util::redirect('index.php');
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
    $def = Definition::get_by_id($defId);
    FlashMessage::add("Definiția „{$d->lexicon}” nu face parte din acest proiect.");
    Util::redirect("?projectId={$projectId}");
  }
  $ar->reviewed = true;
  $ar->errors = $errors;
  $ar->save();

  $project->computeErrorRate();
  $project->save();

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

SmartyWrap::assign([
  'project' => $project,
  'mine' => $mine,
  'def' => $def,
  'errors' => $errors,
  'definitionData' => $defData,
]);
SmartyWrap::addCss('admin');
SmartyWrap::display('acuratete-eval.tpl');
