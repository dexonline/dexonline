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
    $lexems[] = Lexem::get("id = {$if->lexemId}");
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
  $dbResult = db_execute("select distinct I.* from InflectedForm I, Lexem L where I.lexemId = L.id and I.{$field} = '{$cuv}' " .
                         "and L.isLoc order by L.formNoAccent");
  return db_getObjects(new InflectedForm(), $dbResult);
}

?>
