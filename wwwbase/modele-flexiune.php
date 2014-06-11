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

  $lexemData = array();
  foreach ($modelsToDisplay as $m) {
    $lexemData[] = getLexemData($m->exponent, $modelType->code, $m->number, $locVersion);
  }
  
  SmartyWrap::assign('modelsToDisplay', $modelsToDisplay);
  SmartyWrap::assign('lexemData', $lexemData);
} else {
  SmartyWrap::assign('selectedLocVersion', $locVersions[0]->name);
  SmartyWrap::assign('selectedModelType', '');
  SmartyWrap::assign('selectedModelNumber', '');
  // LocVersion::changeDatabase($locVersion);
}

SmartyWrap::assign('page_title', 'Modele de flexiune');
SmartyWrap::assign('locVersions', $locVersions);
SmartyWrap::addCss('paradigm');
SmartyWrap::addJs('modelDropdown');
SmartyWrap::displayCommonPageWithSkin('modele-flexiune.ihtml');

/*************************************************************************/

/**
 * Returns a structure (dictionary / class) with data about a word. We cannot simply use the LexemModel class
 * because it didn't exist before LOC 6.0.
 **/
function getLexemData($form, $modelType, $modelNumber, $locVersion) {
  // Load by canonical model, so if $modelType is V, look for a lexem with type V or VT.
  if ($locVersion >= '6.0') {
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
      $lm->loadInflectedFormsMappedByRank();
    } else {
      $l = Lexem::deepCreate($form, $modelType, $modelNumber);
      $lm = $l->getLexemModels()[0];
      $lm->generateInflectedFormsMappedByRank();
    }
    return $lm;
  } else {
    $l = Model::factory('Lexem')
      ->table_alias('l')
      ->select('l.*')
      ->join('ModelType', 'modelType = code', 'mt')
      ->where('mt.canonical', $modelType)
      ->where('l.modelNumber', $modelNumber)
      ->where('l.form', $form)
      ->limit(1)
      ->find_one();
    if ($l) {
      $ifs = InflectedForm::get_all_by_lexemId($l->id);
      if ($locVersion >= '5.0') {
        $ifMap = InflectedForm::mapByInflectionRank($ifs);
      } else {
        $ifMap = InflectedForm::mapByInflectionId($ifs);
      }
    } else {
      $l = Lexem::deepCreate($form, $modelType, $modelNumber);
      $lm = $l->getLexemModels()[0];
      if ($locVersion >= '5.0') {
        $ifMap = $lm->generateInflectedFormsMappedByRank();
      } else {
        $ifMap = $lm->generateInflectedFormsMappedByInflectionId();
      }
    }
    return array('lexem' => $l,
                 'ifMap' => $ifMap,
                 'modelType' => ModelType::get_by_canonical($modelType));
  }
}

/**
 * Load the forms to display for a model when a lexem already exists. This code is specific to each LOC version.
 */
function getExistingForms($lexemModel, $locVersion) {
  if ($locVersion >= '5.0') {
    return $lexemModel->loadInflectedFormsMappedByRank();
  } else {
    return $lexemModel->loadInflectedFormsMappedByInflectionId();
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
