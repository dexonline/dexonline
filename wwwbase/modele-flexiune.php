<?php
require_once("../phplib/util.php"); 
setlocale(LC_ALL, "ro_RO.utf8");
DebugInfo::disable();

$locVersion = util_getRequestParameter('locVersion');
$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');

$locVersions = pref_getLocVersions();

if ($locVersion && $modelType && $modelNumber) {
  smarty_assign('selectedLocVersion', $locVersion);
  smarty_assign('selectedModelType', $modelType);
  smarty_assign('selectedModelNumber', $modelNumber);

  LocVersion::changeDatabase($locVersion);

  if ($modelNumber == -1) {
    $modelsToDisplay = FlexModel::loadByType($modelType);
  } else {
    $modelsToDisplay = array(Model::factory('FlexModel')->where('modelType', $modelType)->where('number', $modelNumber)->find_one());
  }
  $lexems = array();
  $paradigms = array();

  foreach ($modelsToDisplay as $m) {
    // Load by canonical model, so if $modelType is V, look for a lexem with type V or VT.
    $l = Model::factory('Lexem')->select('Lexem.*')->join('ModelType', 'modelType = code')->where('canonical', $modelType)
      ->where('modelNumber', $m->number)->where('form', $m->exponent)->limit(1)->find_one();

    if ($l) {
      $paradigm = getExistingForms($l->id, $locVersion);
    } else {
      $l = Lexem::create($m->exponent, $modelType, $m->number, '');
      $l->isLoc = true;
      $paradigm = getNewForms($l, $locVersion);
    }
    $lexems[] = $l;
    $paradigms[] = $paradigm;
  }
  
  smarty_assign('modelsToDisplay', $modelsToDisplay);
  smarty_assign('lexems', $lexems);
  smarty_assign('paradigms', $paradigms);
} else {
  smarty_assign('selectedLocVersion', $locVersions[0]->name);
  // LocVersion::changeDatabase($locVersion);
}

$modelTypes = ModelType::loadCanonical();
$models = FlexModel::loadByType($modelType ? $modelType : $modelTypes[0]->code);

smarty_assign('page_title', 'Modele de flexiune');
smarty_assign('locVersions', $locVersions);
smarty_assign('modelTypes', $modelTypes);
smarty_assign('models', $models);
smarty_displayCommonPageWithSkin('modele-flexiune.ihtml');

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
