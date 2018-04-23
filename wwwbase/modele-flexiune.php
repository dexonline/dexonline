<?php
require_once("../phplib/Core.php");

$locVersion = Request::get('locVersion');
$modelType = Request::get('modelType');

$modelType = ModelType::get_by_code($modelType); // Use the ModelType object from this point on

if (!$modelType) {
  FlashMessage::add('Date incorecte.');
  Util::redirect('scrabble');
}
if ($locVersion) {
  LocVersion::changeDatabase($locVersion);
}
$models = FlexModel::loadByType($modelType->code);

$lexemes = [];
foreach ($models as $m) {
  $lexemes[] = $m->getExponentWithParadigm();
}
DB::changeDatabase(DB::$database);

SmartyWrap::addCss('paradigm');
SmartyWrap::assign('models', $models);
SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::assign('locVersion', $locVersion);
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::display('modele-flexiune.tpl');
