<?php
require_once("../../phplib/Core.php");

$query = Request::get('term');
$exclude = Request::get('exclude', 0);

$entries = Model::factory('Entry')
         ->where_like('description', "{$query}%")
         ->where_not_equal('id', $exclude)
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
