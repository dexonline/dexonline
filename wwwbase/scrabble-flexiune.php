<?php
require_once("../phplib/util.php");
$form = util_getRequestParameter('form');
$locVersion = util_getRequestParameter('locVersion');

$locVersions = Config::getLocVersions();

if ($locVersion && $form) {
  LocVersion::changeDatabase($locVersion);
  $form = StringUtil::cleanupQuery($form);
  SmartyWrap::assign('page_title', 'Verificare LOC: ' . $form);

  $data = loadLoc($form, $locVersion);
  SmartyWrap::assign('form', $form);
  SmartyWrap::assign('selectedLocVersion', $locVersion);
  SmartyWrap::assign('data', $data);
} else {
  SmartyWrap::assign('selectedLocVersion', $locVersions[1]->name);
  SmartyWrap::assign('page_title', 'Căutare formă flexionară în LOC ' . $form);
}

setlocale(LC_ALL, "ro_RO.utf8");
SmartyWrap::assign('locVersions', $locVersions);
SmartyWrap::displayCommonPageWithSkin('scrabble-flexiune.ihtml');

/***************************************************************************/

function loadLoc($cuv, $locVersion) {
  $field = StringUtil::hasDiacritics($cuv) ? 'formNoAccent' : 'formUtf8General';

  if ($locVersion >= '6.0') {
    // LOC 6.0 introduces LexemModels
    return Model::factory('InflectedForm')
      ->table_alias('I')
      ->select('I.form', 'inflectedForm')
      ->select('L.id', 'lexemId')
      ->select('L.formNoAccent', 'lexemFormNoAccent')
      ->select('L.form', 'lexemForm')
      ->select('LM.modelType', 'modelType')
      ->select('LM.modelNumber', 'modelNumber')
      ->select('LM.restriction', 'restriction')
      ->select('Infl.description', 'inflection')
      ->join('LexemModel', 'I.lexemModelId = LM.id', 'LM')
      ->join('Lexem', 'LM.lexemId = L.id', 'L')
      ->join('ModelType', 'LM.modelType = MT.code', 'MT')
      ->join('Model', 'MT.canonical = M.modelType and LM.modelNumber = M.number', 'M')
      ->join('ModelDescription', 'M.id = MD.modelId and I.variant = MD.variant and I.inflectionId = MD.inflectionId', 'MD')
      ->join('Inflection', 'I.inflectionId = Infl.id', 'Infl')
      ->where('MD.applOrder', 0)
      ->where("I.{$field}", $cuv)
      ->where('LM.isLoc', 1)
      ->where('MD.isLoc', 1)
      ->order_by_asc('LM.lexemId')
      ->find_array();
  } else {
    return Model::factory('InflectedForm')
      ->table_alias('I')
      ->select('I.form', 'inflectedForm')
      ->select('L.id', 'lexemId')
      ->select('L.formNoAccent', 'lexemFormNoAccent')
      ->select('L.form', 'lexemForm')
      ->select('L.modelType', 'modelType')
      ->select('L.modelNumber', 'modelNumber')
      ->select('L.restriction', 'restriction')
      ->select('Infl.description', 'inflection')
      ->join('Lexem', 'I.lexemId = L.id', 'L')
      ->join('ModelType', 'L.modelType = MT.code', 'MT')
      ->join('Model', 'MT.canonical = M.modelType and L.modelNumber = M.number', 'M')
      ->join('ModelDescription', 'M.id = MD.modelId and I.variant = MD.variant and I.inflectionId = MD.inflectionId', 'MD')
      ->join('Inflection', 'I.inflectionId = Infl.id', 'Infl')
      ->where('MD.applOrder', 0)
      ->where("I.{$field}", $cuv)
      ->where('L.isLoc', 1)
      ->where('MD.isLoc', 1)
      ->order_by_asc('L.id')
      ->find_array();
  }
}

?>
