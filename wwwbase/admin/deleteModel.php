<?php
require_once '../../phplib/Core.php';
ini_set('max_execution_time', '3600');
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();
DebugInfo::disable();

$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$deleteButton = Request::has('deleteButton');

$model = Model::factory('FlexModel')
       ->where('modelType', $modelType)
       ->where('number', $modelNumber)
       ->find_one();
$lexemes = Lexeme::loadByCanonicalModel($modelType, $modelNumber);

if ($deleteButton) {
  foreach ($lexemes as $l) {
    $l->modelType = 'T';
    $l->modelNumber = '1';
    $l->restriction = '';
    $l->save();
    $l->regenerateParadigm();
  }
  Log::warning("Deleting model {$model->id} ({$model})");
  $model->delete();
  FlashMessage::add('Am șters modelul.', 'success');
  Util::redirect('index.php');
}

SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('modelNumber', $modelNumber);
SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::display('admin/deleteModel.tpl');
