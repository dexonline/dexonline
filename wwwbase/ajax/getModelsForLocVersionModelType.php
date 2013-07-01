<?php
require_once("../../phplib/util.php");

$modelType = util_getRequestParameter('modelType');
$locVersion = util_getRequestParameter('locVersion');

if ($locVersion) {
  LocVersion::changeDatabase($locVersion);
}

$models = FlexModel::loadByType($modelType);

$resp = array();
foreach ($models as $m) {
  $resp[] = array('id' => $m->id, 'number' => $m->number, 'exponent' => $m->exponent);
}
print json_encode($resp);

?>
