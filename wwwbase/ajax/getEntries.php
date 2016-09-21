<?php
require_once("../../phplib/util.php");

$query = Request::get('term');

$entries = Model::factory('Entry')
         ->where_like('description', "{$query}%")
         ->order_by_asc('description')
         ->limit(10)
         ->find_many();

$resp = ['results' => []];
foreach ($entries as $e) {
  $resp['results'][] = [
    'id' => $e->id,
    'text' => $e->description,
  ];
}

header('Content-Type: application/json');
print json_encode($resp);

?>
