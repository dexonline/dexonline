<?php
require_once("../../phplib/util.php"); 
ini_set('max_execution_time', '3600');
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();
DebugInfo::disable();

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$deleteButton = util_getRequestParameter('deleteButton');

$model = Model::factory('FlexModel')->where('modelType', $modelType)->where('number', $modelNumber)->find_one();
$lexemModels = LexemModel::loadByCanonicalModel($modelType, $modelNumber);

$locPerm = util_isModerator(PRIV_LOC);
$numLoc = 0;
foreach ($lexemModels as $lm) {
  $numLoc += ($lm->isLoc);
}

if ($numLoc && !$locPerm) {
  FlashMessage::add("Nu puteți șterge acest model, deoarece {$numLoc} dintre " .
                    "lexeme sunt incluse în Lista Oficială de Cuvinte.",
                    'danger');
}

if ($deleteButton) {
  foreach ($lexemModels as $lm) {
    $lm->modelType = 'T';
    $lm->modelNumber = '1';
    $lm->restriction = '';
    $lm->save();
    $lm->regenerateParadigm();
  }
  Log::warning("Deleting model {$model->id} ({$model})");
  $model->delete();
  util_redirect('../admin/index.php');
}

RecentLink::createOrUpdate("Ștergere model: {$model}");
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('modelNumber', $modelNumber);
SmartyWrap::assign('lexemModels', $lexemModels);
SmartyWrap::assign('locPerm', $locPerm);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/deleteModel.tpl');

?>
