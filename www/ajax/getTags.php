<?php
require_once '../../phplib/Core.php';

$query = Request::get('term');
$query = addslashes($query);

$tags = Model::factory('Tag')
      ->where_like('value', "%{$query}%")
      ->order_by_expr("value like '{$query}%' desc") // prefer prefix matches
      ->order_by_asc('value')
      ->limit(10)
      ->find_many();

$resp = ['results' => []];
foreach ($tags as $t) {
  $resp['results'][] = [
    'id' => $t->id,
    'text' => $t->value,
  ];
}

header('Content-Type: application/json');
print json_encode($resp);
