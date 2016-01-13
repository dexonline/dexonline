<?php
require_once("../../phplib/util.php");

$name = util_getRequestParameter('name');

$m = Model::factory('ModelType')
   ->join('Model', ['canonical', '=', 'modelType'])
   ->where_raw("concat(code, number) = '{$name}'")
   ->find_one();

print json_encode("{$m->code}{$m->number} ({$m->exponent})");
