<?php
require_once("../../phplib/Core.php");

// Takes a JSON-encoded list of ids

$jsonIds = Request::get('q');
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
