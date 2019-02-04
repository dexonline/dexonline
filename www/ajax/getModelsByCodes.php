<?php
require_once '../../lib/Core.php';

$codes = Request::getJson('q', []);
$fuzzy = Request::get('fuzzy');
$data = [];

foreach ($codes as $code) {
  $where = $fuzzy
         ? "like \"{$code}%\""
         : "= \"{$code}\"";

  $models = Model::factory('ModelType')
          ->join('Model', ['canonical', '=', 'modelType'])
          ->where_raw("concat(code, number) {$where}")
          ->order_by_asc('code')
          ->order_by_expr('cast(number as unsigned)')
          ->order_by_asc('number')
          ->limit(10)
          ->find_many();

  foreach ($models as $m) {
    $data[] = [
      'id' => "{$m->code}{$m->number}",
      'text' => "{$m->code}{$m->number} ({$m->exponent})",
    ];
  }
}

header('Content-Type: application/json');
print json_encode($data);
