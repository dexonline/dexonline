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
  $hasDiacritics = text_hasDiacritics($form);
  smarty_assign('page_title', 'DEX online - Verificare LOC: ' . $form);

  $wordlists = Wordlist::loadLoc($form, $hasDiacritics);
  $lexems = array();
  $inflections = array();
  foreach ($wordlists as $wl) {
    $lexems[] = Lexem::load($wl->lexemId);
    $inflections[] = Inflection::load($wl->inflectionId);
  }
  smarty_assign('form', $form);
  smarty_assign('selectedLocVersion', $locVersion);
  smarty_assign('wordlists', $wordlists);
  smarty_assign('lexems', $lexems);
  smarty_assign('inflections', $inflections);
}

setlocale(LC_ALL, "ro_RO");
smarty_assign('locVersions', array_reverse(pref_getFrozenLocVersions()));
smarty_displayCommonPageWithSkin('scrabbleCheckInflection.ihtml');

?>
