<?php
require_once("../../phplib/Core.php");

// Takes a JSON-encoded list of ids

$jsonIds = Request::get('q');
$ids = json_decode($jsonIds);
$data = [];

foreach ($ids as $id) {
  $s = Source::get_by_id($id);

  if ($s) {
    $data[] = [
      'id' => $s->id,
      'text' => $s->shortName,
    ];
  }
}

header('Content-Type: application/json');
print json_encode($data);
