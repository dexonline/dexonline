<?php
require_once("../phplib/util.php"); 
setlocale(LC_ALL, "ro_RO.utf8");
DebugInfo::disable();

$locVersion = util_getRequestParameter('locVersion');
$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');

$locVersions = Config::getLocVersions();
$modelType = ModelType::get_by_code($modelType); // Use the ModelType object from this point on

if ($locVersion && $modelType && $modelNumber) {
  SmartyWrap::assign('selectedLocVersion', $locVersion);
  SmartyWrap::assign('selectedModelType', $modelType->code);
  SmartyWrap::assign('selectedModelNumber', $modelNumber);

  LocVersion::changeDatabase($locVersion);

  if ($modelNumber == -1) {
    $modelsToDisplay = FlexModel::loadByType($modelType->code);
  } else {
    $modelsToDisplay = array(Model::factory('FlexModel')->where('modelType', $modelType->code)->where('number', $modelNumber)->find_one());
  }

  $lexemModels = array();
  foreach ($modelsToDisplay as $m) {
    $lexemModels[] = getLexemModel($m->exponent, $modelType->code, $m->number);
  }
  
  SmartyWrap::assign('modelsToDisplay', $modelsToDisplay);
  SmartyWrap::assign('lexemModels', $lexemModels);
} else {
  SmartyWrap::assign('selectedLocVersion', $locVersions[0]->name);
  SmartyWrap::assign('selectedModelType', '');
  SmartyWrap::assign('selectedModelNumber', '');
}

SmartyWrap::assign('page_title', 'Modele de flexiune');
SmartyWrap::assign('locVersions', $locVersions);
SmartyWrap::addCss('paradigm');
SmartyWrap::addJs('modelDropdown');
SmartyWrap::displayCommonPageWithSkin('modele-flexiune.ihtml');

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
