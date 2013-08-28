<?php
require_once("../phplib/util.php"); 
setlocale(LC_ALL, "ro_RO.utf8");
DebugInfo::disable();

$locVersion = util_getRequestParameter('locVersion');
$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');

$locVersions = pref_getLocVersions();
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
  $lexems = array();
  $paradigms = array();

  foreach ($modelsToDisplay as $m) {
    // Load by canonical model, so if $modelType is V, look for a lexem with type V or VT.
    $l = Model::factory('Lexem')->select('Lexem.*')->join('ModelType', 'modelType = code', 'mt')->where('mt.canonical', $modelType->code)
      ->where('modelNumber', $m->number)->where('form', $m->exponent)->limit(1)->find_one();

    if ($l) {
      $paradigm = getExistingForms($l->id, $locVersion);
    } else {
      $l = Lexem::create($m->exponent, $modelType->code, $m->number, '');
      $l->isLoc = true;
      $paradigm = getNewForms($l, $locVersion);
    }
    $lexems[] = $l;
    $paradigms[] = $paradigm;
  }
  
  SmartyWrap::assign('modelsToDisplay', $modelsToDisplay);
  SmartyWrap::assign('lexems', $lexems);
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
function getExistingForms($lexemId, $locVersion) {
  if ($locVersion >= '5.0') {
    return InflectedForm::loadByLexemIdMapByInflectionRank($lexemId);
  } else {
    return InflectedForm::loadByLexemIdMapByInflectionId($lexemId);
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
