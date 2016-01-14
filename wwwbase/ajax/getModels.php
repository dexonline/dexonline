<?php
require_once("../../phplib/util.php");

$term = util_getRequestParameter('term');
$exact = util_getRequestParameter('exact');

$where = $exact? "= \"{$term}\"" : "like \"{$term}%\""; 

$models = Model::factory('ModelType')
        ->join('Model', ['canonical', '=', 'modelType'])
        ->where_raw("concat(code, number) {$where}")
        ->order_by_asc('code')
        ->order_by_expr('cast(number as unsigned)')
        ->order_by_asc('number')
        ->limit(10)
        ->find_many();

$resp = ['results' => []];
foreach ($models as $m) {
  $id = "{$m->code}{$m->number}";
  $text = "{$m->code}{$m->number} ({$m->exponent})";
  $resp['results'][] = ['id' => $id, 'text' => $text];
}
print json_encode($resp);
