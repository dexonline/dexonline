<?
require_once("../phplib/util.php");
$locVersion = util_getRequestParameter('locVersion');

if ($locVersion) {
  $lv = new LocVersion();
  $lv->name = $locVersion;
  $dbName = pref_getLocPrefix() . $lv->getDbName();
  db_changeDatabase($dbName);

  header('Content-type: text/plain; charset=UTF-8');
  $dbResult = db_execute("select * from Lexem where isLoc order by formNoAccent");
  while (!$dbResult->EOF) {
    $l = new Lexem();
    $l->set($dbResult->fields);
    $dbResult->MoveNext();
    print text_padRight(text_unicodeToUpper($l->form), 20);
    print text_padRight($l->modelType, 4);
    print text_padRight($l->modelNumber, 8);
    print $l->restriction . "\n";
  }
  return;
}

setlocale(LC_ALL, "ro_RO");
smarty_assign('locVersions', array_reverse(pref_getFrozenLocVersions()));
smarty_displayCommonPageWithSkin('scrabble-loc.ihtml');


?>
