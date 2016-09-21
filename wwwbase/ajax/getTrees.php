<?php
require_once("../../phplib/util.php");

$query = Request::get('term');

$trees = Model::factory('Tree')
       ->where_like('description', "{$query}%")
       ->where('status', Tree::ST_VISIBLE)
       ->order_by_asc('description')
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

?>
