<?php
require_once("../../phplib/util.php");

$locVersion = util_getRequestParameter('locVersion');
LocVersion::changeDatabase($locVersion);
$modelTypes = ModelType::loadCanonical();
$resp = array();
foreach ($modelTypes as $m) {
  $resp[] = array('code' => $m->code, 'description' => $m->description);
}
print json_encode($resp);

?>
