<?php
require_once '../../lib/Core.php';

$query = Request::get('term');
$exclude = Request::get('exclude', 0);
$unstructured = Request::has('unstructured');

$query = addslashes($query);

$entries = Model::factory('Entry')
  ->table_alias('e')
  ->select('e.*')
  ->select('l.formNoAccent')
  ->distinct()
  ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
  ->join('Lexeme', ['el.lexemeId', '=', 'l.id'], 'l')
  ->where_like('l.formNoAccent', "{$query}%")
  ->where_not_equal('e.id', $exclude);

if ($unstructured) {
  $entries = $entries->where_in('e.structStatus', [
    Entry::STRUCT_STATUS_NEW,
    Entry::STRUCT_STATUS_IN_PROGRESS,
  ]);
}

$entries = $entries
  ->order_by_expr("l.formNoAccent = '{$query}' desc") // prefer exact matches
  ->order_by_expr("e.description like '{$query}%' desc") // then prefer prefix matches
  ->order_by_asc('e.description')
  ->limit(20)
  ->find_many();

// Filter entries here by case-sensitive prefix. MySQL case-sensitive indices
// are considerably slower.
$entries = array_filter($entries, function($e) use ($query) {
  return Str::startsWith($e->formNoAccent, $query);
});

$resp = ['results' => []];
foreach ($entries as $e) {
  $resp['results'][] = [
    'id' => $e->id,
    'text' => $e->description,
  ];
}

header('Content-Type: application/json');
print json_encode($resp);
