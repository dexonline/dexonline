<?php
require_once("../phplib/util.php");
$form = util_getRequestParameter('form');
$locVersion = util_getRequestParameter('locVersion');

$locVersions = pref_getLocVersions();

if ($locVersion && $form) {
  LocVersion::changeDatabase($locVersion);
  $form = text_cleanupQuery($form);
  smarty_assign('page_title', 'Verificare LOC: ' . $form);

  $ifs = loadLoc($form);
  $lexems = array();
  $inflections = array();
  foreach ($ifs as $if) {
    $lexems[] = Lexem::get("id = {$if->lexemId}");
    $inflections[] = Inflection::get("id = {$if->inflectionId}");
  }
  smarty_assign('form', $form);
  smarty_assign('selectedLocVersion', $locVersion);
  smarty_assign('ifs', $ifs);
  smarty_assign('lexems', $lexems);
  smarty_assign('inflections', $inflections);
} else {
  smarty_assign('selectedLocVersion', $locVersions[1]->name);
  smarty_assign('page_title', 'Căutare formă flexionară în LOC ' . $form);
}

setlocale(LC_ALL, "ro_RO.utf8");
smarty_assign('locVersions', $locVersions);
smarty_displayCommonPageWithSkin('scrabble-flexiune.ihtml');


function loadLoc($cuv) {
  $field = text_hasDiacritics($cuv) ? 'formNoAccent' : 'formUtf8General';
  $result = array();
  $dbResult = db_execute("select distinct I.* from InflectedForm I, Lexem L, Model M, ModelDescription MD, ModelType MT " .
			 "where I.lexemId = L.id and L.modelType = MT.code and MT.canonical = M.modelType and L.modelNumber = M.number and M.id = MD.modelId " .
			 "and I.inflectionId = MD.inflectionId and I.variant = MD.variant and MD.applOrder = 0 " .
			 "and I.{$field} = '{$cuv}' and L.isLoc and MD.isLoc order by L.formNoAccent");
  return db_getObjects(new InflectedForm(), $dbResult);
}

?>
