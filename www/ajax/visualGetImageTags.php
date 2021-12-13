<?php

require_once '../../lib/Core.php';

$visualId = Request::get('visualId');
$page = Request::get('page');
$limit = Request::get('rows');
$resp = [];
$tags = [];

$total = Model::factory('VisualTag')
  ->where('imageId', $visualId)
  ->count();
$lines = Model::factory('VisualTag')
  ->where('imageId', $visualId)
  ->limit($limit)->offset(($page - 1) * $limit)
  ->find_many();

foreach ($lines as $line) {
  $entry = Entry::get_by_id($line->entryId);
  $tags[] = [
    'id' => $line->id,
    'label' => $line->label,
    'labelX' => $line->labelX,
    'labelY' => $line->labelY,
    'tipX' => $line->tipX,
    'tipY' => $line->tipY,
    'entry' => $entry->description,
    'entryId' => $entry->id,
  ];
}

$resp = [
  'total' => ceil($total / $limit),
  'page' => $page,
  'records' => $total,
  'rows' => $tags,
];

header('Content-Type: application/json');
echo json_encode($resp);
