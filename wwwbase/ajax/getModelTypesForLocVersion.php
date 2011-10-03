<?php
require_once("../../phplib/util.php");

$locVersion = util_getRequestParameter('locVersion');
LocVersion::changeDatabase($locVersion);
$modelTypes = ModelType::loadCanonical();
foreach ($modelTypes as $m) {
  print "{$m->code}\n{$m->description}\n";
}

?>
