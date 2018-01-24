<?php
require_once("../phplib/Core.php"); 

$locVersion = Request::get('locVersion') || NULL;
$modelType = Request::get('modelType');

$modelType = ModelType::get_by_code($modelType); // Use the ModelType object from this point on

if (!$modelType) {
  FlashMessage::add('Date incorecte.');
  Util::redirect('scrabble');
}
if ($locVersion) LocVersion::changeDatabase($locVersion);
$models = FlexModel::loadByType($modelType->code);

$lexemes = [];
foreach ($models as $m) {
  $lexemes[] = getLexeme($m->exponent, $modelType->code, $m->number);
}
DB::changeDatabase(DB::$database);

SmartyWrap::addCss('paradigm');
SmartyWrap::assign('models', $models);
SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::assign('locVersion', $locVersion);
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::display('modele-flexiune.tpl');

/*************************************************************************/

/**
 * Returns a lexeme for a given word and model. Creates one if one doesn't exist.
 **/
function getLexeme($form, $modelType, $modelNumber) {
  // Load by canonical model, so if $modelType is V, look for a lexeme with type V or VT.
  $l = Model::factory('Lexeme')
     ->table_alias('l')
     ->select('l.*')
     ->join('ModelType', 'modelType = code', 'mt')
     ->where('mt.canonical', $modelType)
     ->where('l.modelNumber', $modelNumber)
     ->where('l.form', $form)
     ->limit(1)
     ->find_one();
  if ($l) {
    $l->loadInflectedFormMap();
  } else {
    $l = Lexeme::create($form, $modelType, $modelNumber);
    $l->setAnimate(true);
    $l->generateInflectedFormMap();
  }
  return $l;
}
