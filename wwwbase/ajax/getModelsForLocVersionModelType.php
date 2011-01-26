<?
require_once("../../phplib/util.php");

$modelType = util_getRequestParameter('modelType');
$locVersion = util_getRequestParameter('locVersion');

if ($locVersion) {
  LocVersion::changeDatabase($locVersion);
}

$models = Model::loadByType($modelType);

foreach ($models as $m) {
  print "{$m->id}\n{$m->number}\n{$m->exponent}\n";
}

?>
