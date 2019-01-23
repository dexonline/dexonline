<?php
require_once("../../phplib/Core.php");

$query = Request::get('term');
$query = addslashes($query);

$trees = Model::factory('Tree')
       ->where_like('descriptionSort', "{$query}%") // case sensitive
       ->where('status', Tree::ST_VISIBLE)
       ->order_by_asc('descriptionSort')
       ->limit(10)
       ->find_many();

$resp = ['results' => []];
foreach ($trees as $t) {
  $resp['results'][] = [
    'id' => $t->id,
    'text' => $t->description,
  ];
}

header('Content-Type: application/json');
print json_encode($resp);
