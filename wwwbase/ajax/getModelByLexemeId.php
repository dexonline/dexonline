<?php
require_once("../../phplib/Core.php");

$id = Request::get('id');

$l = Lexem::get_by_id($id);
$mt = ModelType::get_by_code($l->modelType);
$m = FlexModel::get_by_modelType_number($mt->canonical, $l->modelNumber);

$results = [
  'modelType' => $l->modelType,
  'modelNumber' => $l->modelNumber,
  'restriction' => $l->restriction,
  'exponent' => $m->exponent,
];

header('Content-Type: application/json');
print json_encode($results);
