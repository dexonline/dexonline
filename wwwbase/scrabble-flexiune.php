<?php
require_once("../phplib/util.php");
$form = util_getRequestParameter('form');
$locVersion = util_getRequestParameter('locVersion');

$locVersions = Config::getLocVersions();

if ($locVersion && $form) {
  LocVersion::changeDatabase($locVersion);
  $form = StringUtil::cleanupQuery($form);
  $field = StringUtil::hasDiacritics($form) ? 'formNoAccent' : 'formUtf8General';
  $data = Model::factory('InflectedForm')
    ->table_alias('I')
    ->select('I.form', 'inflectedForm')
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
    ->where("I.{$field}", $form)
    ->where('LM.isLoc', 1)
    ->where('MD.isLoc', 1)
    ->order_by_asc('LM.lexemId')
    ->find_array();

  SmartyWrap::assign('page_title', 'Verificare LOC: ' . $form);
  SmartyWrap::assign('form', $form);
  SmartyWrap::assign('selectedLocVersion', $locVersion);
  SmartyWrap::assign('data', $data);
} else {
  SmartyWrap::assign('selectedLocVersion', $locVersions[1]->name);
  SmartyWrap::assign('page_title', 'Căutare formă flexionară în LOC ' . $form);
}

setlocale(LC_ALL, "ro_RO.utf8");
SmartyWrap::assign('locVersions', $locVersions);
SmartyWrap::display('scrabble-flexiune.ihtml');

?>
