<?php
require_once("../../phplib/util.php"); 
ini_set('max_execution_time', '3600');
User::require(User::PRIV_EDIT);
util_assertNotMirror();
DebugInfo::disable();

$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$deleteButton = Request::has('deleteButton');

$model = Model::factory('FlexModel')
       ->where('modelType', $modelType)
       ->where('number', $modelNumber)
       ->find_one();
$lexems = Lexem::loadByCanonicalModel($modelType, $modelNumber);

$locPerm = User::can(User::PRIV_LOC);
$numLoc = 0;
foreach ($lexems as $l) {
  $numLoc += ($l->isLoc);
}

if ($numLoc && !$locPerm) {
  FlashMessage::add("Nu puteți șterge acest model, deoarece {$numLoc} dintre " .
                    "lexeme sunt incluse în Lista Oficială de Cuvinte.",
                    'danger');
}

if ($deleteButton) {
  foreach ($lexems as $l) {
    $l->modelType = 'T';
    $l->modelNumber = '1';
    $l->restriction = '';
    $l->save();
    $l->regenerateParadigm();
  }
  Log::warning("Deleting model {$model->id} ({$model})");
  $model->delete();
  FlashMessage::add('Am șters modelul.', 'success');
  util_redirect('index.php');
}

SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('modelNumber', $modelNumber);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('locPerm', $locPerm);
SmartyWrap::display('admin/deleteModel.tpl');

?>
