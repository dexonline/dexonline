<?php
require_once '../../lib/Core.php';

$modelType = Request::get('modelType');

$models = FlexModel::loadByType($modelType);

$resp = [];
foreach ($models as $m) {
  $resp[] = ['id' => $m->id, 'number' => $m->number, 'exponent' => $m->exponent];
}
print json_encode($resp);
