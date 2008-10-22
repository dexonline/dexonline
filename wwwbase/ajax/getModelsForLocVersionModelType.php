<?
require_once("../../phplib/util.php");

$modelType = util_getRequestParameter('modelType');
$locVersion = util_getRequestParameter('locVersion');

if ($locVersion) {
  $lv = new LocVersion();
  $lv->name = $locVersion;
  $dbName = pref_getLocPrefix() . $lv->getDbName();
  db_changeDatabase($dbName);
}

$models = Model::loadByType($modelType);

foreach ($models as $m) {
  print "{$m->id}\n{$m->number}\n{$m->exponent}\n";
}

?>
