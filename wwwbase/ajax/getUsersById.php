<?php
require_once("../../phplib/Core.php");

// Takes a JSON-encoded list of ids

$jsonIds = Request::get('q');
$ids = json_decode($jsonIds);
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

?>
