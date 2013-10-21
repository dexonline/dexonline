<?php
require_once("../phplib/util.php");
$form = util_getRequestParameter('form');
$locVersion = util_getRequestParameter('locVersion');

$locVersions = Config::getLocVersions();

if ($locVersion && $form) {
  LocVersion::changeDatabase($locVersion);
  $form = StringUtil::cleanupQuery($form);
  SmartyWrap::assign('page_title', 'Verificare LOC: ' . $form);

  $ifs = loadLoc($form);
  $lexems = array();
  $inflections = array();
  foreach ($ifs as $if) {
    $lexems[] = Lexem::get_by_id($if->lexemId);
    $inflections[] = Inflection::get_by_id($if->inflectionId);
  }
  SmartyWrap::assign('form', $form);
  SmartyWrap::assign('selectedLocVersion', $locVersion);
  SmartyWrap::assign('ifs', $ifs);
  SmartyWrap::assign('lexems', $lexems);
  SmartyWrap::assign('inflections', $inflections);
} else {
  SmartyWrap::assign('selectedLocVersion', $locVersions[1]->name);
  SmartyWrap::assign('page_title', 'Căutare formă flexionară în LOC ' . $form);
}

setlocale(LC_ALL, "ro_RO.utf8");
SmartyWrap::assign('locVersions', $locVersions);
SmartyWrap::displayCommonPageWithSkin('scrabble-flexiune.ihtml');


function loadLoc($cuv) {
  $field = StringUtil::hasDiacritics($cuv) ? 'formNoAccent' : 'formUtf8General';
  return Model::factory('InflectedForm')
    ->raw_query("select distinct I.* from InflectedForm I, Lexem L, Model M, ModelDescription MD, ModelType MT " .
                "where I.lexemId = L.id and L.modelType = MT.code and MT.canonical = M.modelType and L.modelNumber = M.number and M.id = MD.modelId " .
                "and I.inflectionId = MD.inflectionId and I.variant = MD.variant and MD.applOrder = 0 " .
                "and I.{$field} = '{$cuv}' and L.isLoc and MD.isLoc order by L.formNoAccent", null)
    ->find_many();
}

?>
