<?php

require_once('../../phplib/Core.php');
User::mustHave(User::PRIV_WOTD);
Util::assertNotMirror();

$page = Request::get('page');
$rows = Request::get('rows');
$sidx = Request::get('sidx') ?: 'displayDate';
$sord = Request::get('sord') ?: 'desc';
$filters = Request::getJson('filters', null, true);

// base query
$query = Model::factory('WordOfTheDay')
       ->table_alias('w')
       ->select('d.lexicon')
       ->select('s.shortName')
       ->select('d.internalRep')
       ->select('w.displayDate')
       ->select('u.name')
       ->select('w.priority')
       ->select('w.image')
       ->select('w.description')
       ->select('w.id')
       ->select('d.id', 'definitionId')
       ->select('d.sourceId')
       ->left_outer_join('Definition', ['w.definitionId', '=', 'd.id'], 'd')
       ->left_outer_join('Source', ['d.sourceId', '=', 's.id'], 's')
       ->join('User', ['w.userId', '=', 'u.id'], 'u');

// filters
foreach ($filters['rules'] ?? [] as $filter) {
  $query = $query->where_like($filter['field'], '%' . $filter['data'] . '%');
}

// get the row count and the actual data
$records = $query->count();
$data = $query
      ->order_by_expr("{$sidx} {$sord}")
      ->limit($rows)
      ->offset(($page - 1) * $rows)
      ->find_array();

// compute HTML for some fields
foreach ($data as &$row) {
  $row['defHtml'] = Str::htmlize($row['internalRep'], $row['sourceId'])[0];
  $row['wotdHtml'] = Str::htmlize($row['description'])[0];
}

// prepare the results
$result = [
  'page' => $page, // current page
  'total' => ceil($records / $rows), // total number of pages
  'records' => $records, // total number of records
  'rows' => $data,
];

header('Content-type: application/json;charset=utf-8');
print json_encode($result);
