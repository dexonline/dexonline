<?php
require_once '../../phplib/Core.php';

$ids = Request::getJson('q', []);
$data = [];

foreach ($ids as $id) {
  $t = Tag::get_by_id($id);

  $data[] = [
    'id' => $t->id,
    'text' => $t->value,
  ];
}

header('Content-Type: application/json');
print json_encode($data);
