<?php
require_once("../../phplib/util.php");

// Takes a JSON-encoded list of ids

$jsonIds = util_getRequestParameter('q');
$ids = json_decode($jsonIds);
$data = [];

foreach ($ids as $id) {
  $t = Tree::get_by_id($id);

  $data[] = [
    'id' => $t->id,
    'text' => $t->description,
  ];
}

header('Content-Type: application/json');
print json_encode($data);

?>
