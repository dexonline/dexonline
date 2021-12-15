<?php
User::mustHave(User::PRIV_WOTD);

// for filters where the submitted field name does not match our query
const FILTERS = [
  'defHtml' => 'd.internalRep',
  'descHtml' => 'w.description',
  'lexicon' => 'd.lexicon',
  'shortName' => 's.shortName',
  'userName' => 'u.name',
];

function deleteWotd() {
  $wotdId = Request::get('wotdId');
  WordOfTheDay::delete_all_by_id($wotdId);
}

function loadWotds() {
  $page = Request::get('page');
  $size = Request::get('size'); // number of rows per page
  $sort = Request::get('sort', []);
  $filters = Request::get('filter', []);

  // base query
  $query = Model::factory('WordOfTheDay')
    ->table_alias('w')
    ->select('w.id')
    ->select('d.id', 'definitionId')
    ->select('d.lexicon')
    ->select('d.internalRep')
    ->select('s.shortName')
    ->select('w.displayDate')
    ->select('u.name', 'userName')
    ->select('w.priority')
    ->select('w.image')
    ->select('w.description')
    ->select('d.sourceId')
    ->left_outer_join('Definition', ['w.definitionId', '=', 'd.id'], 'd')
    ->left_outer_join('Source', ['d.sourceId', '=', 's.id'], 's')
    ->join('User', ['w.userId', '=', 'u.id'], 'u');

  foreach ($filters as $f) {
    // we currently ignore a third parameter, $f['type']
    $field = FILTERS[$f['field']] ?? $f['field'];
    $query = $query->where_like($field, '%' . $f['value'] . '%');
  }

  $records = $query->count();

  foreach ($sort as $crit) {
    $query = $query->order_by_expr($crit['field'] . ' ' . $crit['dir']);
  }
  //  $query = $query->order_by_desc('displayDate');
  $data = $query
    ->limit($size)
    ->offset(($page - 1) * $size)
    ->find_array();

  // process some fields
  foreach ($data as &$row) {
    // add HTML
    $row['defHtml'] = Str::htmlize($row['internalRep'], $row['sourceId'])[0];
    $row['descHtml'] = Str::htmlize($row['description'])[0];

    // shorten user names
    $row['userName'] = preg_replace('/\p{Ll}+$/u', '.', $row['userName']);
  }

  $resp = [
    'last_page' => ceil($records / $size),
    'data' => $data,
  ];

  header('Content-Type: application/json');
  echo json_encode($resp);
}

function saveField() {
  $wotdId = Request::get('wotdId');
  $field = Request::get('field');
  $value = Request::get('value');

  Log::info('got %d %s=%s', $wotdId, $field, $value);

  $wotd = WordOfTheDay::get_by_id($wotdId);
  if ($wotd) {
    $wotd->$field = $value;
    $wotd->save();
  }
}

function main() {
  $action = Request::get('action');

  switch ($action) {
    case 'load': loadWotds(); break;
    case 'delete': deleteWotd(); break;
    case 'save': saveField(); break;
  }
}

main();
