<?php
require_once '../../phplib/Core.php';

$ids = Request::getJson('q', []);
$data = [];

foreach ($ids as $id) {
  $u = User::get_by_id($id);

  $data[] = [
    'id' => $u->id,
    'text' => "{$u->nick} ({$u->name})",
  ];
}

header('Content-Type: application/json');
print json_encode($data);
