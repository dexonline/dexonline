<?php
require_once("../../phplib/util.php");

$locVersion = util_getRequestParameter('locVersion');
$canonical = util_getRequestParameter('canonical');
LocVersion::changeDatabase($locVersion);

if ($canonical) {
  $modelTypes = ModelType::loadCanonical();
} else {
  $modelTypes = Model::factory('ModelType')->order_by_asc('code')->find_many();
}

$resp = array();
foreach ($modelTypes as $m) {
  $resp[] = array('code' => $m->code, 'description' => $m->description);
}
print json_encode($resp);

?>
