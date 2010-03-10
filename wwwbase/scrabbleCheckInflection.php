<?
require_once("../phplib/util.php");
$form = util_getRequestParameter('form');
$locVersion = util_getRequestParameter('locVersion');

if ($locVersion && $form) {
  $lv = new LocVersion();
  $lv->name = $locVersion;
  $dbName = pref_getLocPrefix() . $lv->getDbName();
  db_changeDatabase($dbName);

  $form = text_cleanupQuery($form);
  smarty_assign('page_title', 'DEX online - Verificare LOC: ' . $form);

  $ifs = loadLoc($form);
  $lexems = array();
  $inflections = array();
  foreach ($ifs as $if) {
    $lexems[] = Lexem::load($if->lexemId);
    $inflections[] = Inflection::get("id = {$if->inflectionId}");
  }
  smarty_assign('form', $form);
  smarty_assign('selectedLocVersion', $locVersion);
  smarty_assign('ifs', $ifs);
  smarty_assign('lexems', $lexems);
  smarty_assign('inflections', $inflections);
}

setlocale(LC_ALL, "ro_RO");
smarty_assign('locVersions', array_reverse(pref_getFrozenLocVersions()));
smarty_displayCommonPageWithSkin('scrabbleCheckInflection.ihtml');


function loadLoc($cuv) {
  $field = text_hasDiacritics($cuv) ? 'formNoAccent' : 'formUtf8General';
  $result = array();
  $dbResult = db_execute("select distinct i.* from InflectedForm i, lexems where lexemId = lexem_id and {$field} = '{$cuv}' " .
                         "and lexem_is_loc order by lexem_neaccentuat");
  while (!$dbResult->EOF) {
    $if = new InflectedForm();
    $if->set($dbResult->fields);
    $result[] = $if;
    $dbResult->MoveNext();
  }
  return $result;
}

?>
