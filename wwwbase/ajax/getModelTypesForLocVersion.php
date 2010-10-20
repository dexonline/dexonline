<?
require_once("../../phplib/util.php");

$locVersion = util_getRequestParameter('locVersion');
$lv = new LocVersion();
$lv->name = $locVersion;
$dbName = pref_getLocPrefix() . $lv->getDbName();
db_changeDatabase($dbName);

$modelTypes = ModelType::loadCanonical();
foreach ($modelTypes as $m) {
  print "{$m->code}\n{$m->description}\n";
}

?>
