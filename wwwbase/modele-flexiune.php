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
  SmartyWrap::assign('selectedModelType', $modelType);
  SmartyWrap::assign('selectedModelNumber', $modelNumber);

  LocVersion::changeDatabase($locVersion);

  if ($modelNumber == -1) {
    $modelsToDisplay = FlexModel::loadByType($modelType->code);
  } else {
    $modelsToDisplay = array(Model::factory('FlexModel')->where('modelType', $modelType->code)->where('number', $modelNumber)->find_one());
  }
  $lexemModels = array();
  $paradigms = array();

  foreach ($modelsToDisplay as $m) {
    // Load by canonical model, so if $modelType is V, look for a lexem with type V or VT.
    $lm = Model::factory('LexemModel')
      ->select('lm.*')
      ->table_alias('lm')
      ->join('Lexem', 'l.id = lm.lexemId', 'l')
      ->join('ModelType', 'modelType = code', 'mt')
      ->where('mt.canonical', $modelType->code)
      ->where('lm.modelNumber', $m->number)
      ->where('l.form', $m->exponent)
      ->limit(1)
      ->find_one();

    if ($lm) {
      $paradigm = getExistingForms($lm, $locVersion);
    } else {
      /****************** Generate a lexem with a single lexemModel ********************/
      /* $lm = LexemModel::create($modelType->code, $m->number); */
      /* $lm->isLoc = true; */
      /* $paradigm = getNewForms($lm, $locVersion); */
      $lm = null;
      $paradigm = null;
    }
    $lexemModels[] = $lm;
    $paradigms[] = $paradigm;
  }
  
  SmartyWrap::assign('modelsToDisplay', $modelsToDisplay);
  SmartyWrap::assign('lexemModels', $lexemModels);
  SmartyWrap::assign('paradigms', $paradigms);
} else {
  SmartyWrap::assign('selectedLocVersion', $locVersions[0]->name);
  // LocVersion::changeDatabase($locVersion);
}

$modelTypes = ModelType::loadCanonical();
$models = FlexModel::loadByType($modelType ? $modelType->code : $modelTypes[0]->code);

SmartyWrap::assign('page_title', 'Modele de flexiune');
SmartyWrap::assign('locVersions', $locVersions);
SmartyWrap::assign('modelTypes', $modelTypes);
SmartyWrap::assign('models', $models);
SmartyWrap::addCss('paradigm');
SmartyWrap::addJs('flex');
SmartyWrap::displayCommonPageWithSkin('modele-flexiune.ihtml');

/*************************************************************************/

/**
 * Load the forms to display for a model when a lexem already exists. This code is specific to each LOC version.
 */
function getExistingForms($lexemModel, $locVersion) {
  if ($locVersion >= '5.0') {
    return $lexemModel->getInflectedFormsMappedByRank();
  } else {
    return $lexemModel->getInflectedFormsMappedByInflectionId();
  }
}

function getNewForms($lexem, $locVersion) {
  $ifArray = $lexem->generateParadigm();
  if ($locVersion >= '5.0') {
    return InflectedForm::mapByInflectionRank($ifArray);
  } else {
    return InflectedForm::mapByInflectionId($ifArray);
  }
}
  
?>
