<?php
require_once("../phplib/util.php"); 
setlocale(LC_ALL, "ro_RO.utf8");
DebugInfo::disable();

$locVersion = util_getRequestParameter('locVersion');
$modelType = util_getRequestParameter('modelType');

$modelType = ModelType::get_by_code($modelType); // Use the ModelType object from this point on

if (!$locVersion || !$modelType) {
  FlashMessage::add('Date incorecte');
  util_redirect('scrabble');
}

LocVersion::changeDatabase($locVersion);
$models = FlexModel::loadByType($modelType->code);

$lexemModels = array();
foreach ($models as $m) {
  $lexemModels[] = getLexemModel($m->exponent, $modelType->code, $m->number);
}
  
SmartyWrap::addCss('paradigm');
SmartyWrap::assign('models', $models);
SmartyWrap::assign('lexemModels', $lexemModels);
SmartyWrap::assign('locVersion', $locVersion);
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::display('modele-flexiune.tpl');

/*************************************************************************/

/**
 * Returns a LexemModel for a given word and model. Creates one if one doesn't exist.
 **/
function getLexemModel($form, $modelType, $modelNumber) {
  // Load by canonical model, so if $modelType is V, look for a lexem with type V or VT.
  $lm = Model::factory('LexemModel')
    ->table_alias('lm')
    ->select('lm.*')
    ->join('Lexem', 'l.id = lm.lexemId', 'l')
    ->join('ModelType', 'modelType = code', 'mt')
    ->where('mt.canonical', $modelType)
    ->where('lm.modelNumber', $modelNumber)
    ->where('l.form', $form)
    ->limit(1)
    ->find_one();
  if ($lm) {
    $lm->loadInflectedFormMap();
  } else {
    $l = Lexem::deepCreate($form, $modelType, $modelNumber);
    $lm = $l->getFirstLexemModel();
    $lm->generateInflectedFormMap();
  }
  return $lm;
}

?>
