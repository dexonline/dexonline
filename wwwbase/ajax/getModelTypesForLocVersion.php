<?php
require_once("../../phplib/Core.php");

$locVersion = Request::get('locVersion');
$canonical = Request::get('canonical');
LocVersion::changeDatabase($locVersion);

if ($canonical) {
  $modelTypes = ModelType::loadCanonical();
} else {
  $modelTypes = Model::factory('ModelType')->order_by_asc('code')->find_many();
}

$resp = [];
foreach ($modelTypes as $m) {
  $resp[] = ['code' => $m->code, 'description' => $m->description];
}
print json_encode($resp);
