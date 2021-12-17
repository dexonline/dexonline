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

/**
 * A multipurpose DB query that can load one WotD by ID OR load a page of
 * WotD's after aplying sorting and filters. In either case, it computes some
 * HTML fields for grid display.
 */
function getWotdData($wotdId = null, $page = 1, $size = 1, $sort = [], $filters = [])  {
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

  if ($wotdId) {
    $query = $query->where('w.id', $wotdId);
  }

  foreach ($filters as $f) {
    // we currently ignore a third parameter, $f['type']
    $field = FILTERS[$f['field']] ?? $f['field'];
    $query = $query->where_like($field, '%' . $f['value'] . '%');
  }

  $count = $query->count();

  foreach ($sort as $crit) {
    $query = $query->order_by_expr($crit['field'] . ' ' . $crit['dir']);
  }
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

  return [
    'count' => $count, // total number of pages
    'data' => $data,   // data for this page
  ];
}

function loadWotds() {
  $page = Request::get('page');
  $size = Request::get('size'); // number of rows per page
  $sort = Request::get('sort', []);
  $filters = Request::get('filter', []);

  $results = getWotdData(null, $page, $size, $sort, $filters);

  return [
    'last_page' => ceil($results['count'] / $size),
    'data' => $results['data'],
  ];
}

function deleteWotd() {
  $wotdId = Request::get('wotdId');
  $wotd = WordOfTheDay::get_by_id($wotdId);
  $today = date('Y-m-d');

  if (!$wotd) {
    $error = 'Înregistrarea nu există';
  } else if ($wotd->isPast()) {
    $error = 'Nu puteți ștergere cuvinte deja afișate.';
  } else {
    $wotd->delete();
    $error = null;
  }

  return [ 'error' => $error ];
}

// returns a sanitized date string on success, false on failure
function sanitizeDate(string $s) {
  if ($s == '') {
    return ''; // empty dates allowed
  }

  if (!preg_match('/^\d\d\d\d-\d?\d-\d?\d$/', $s)) {
    return false;
  }

  $parts = explode('-', $s);
  return sprintf('%s-%02d-%02d', $parts[0], $parts[1], $parts[2]);
}

function saveWotd() {
  $definitionId = Request::get('definitionId');
  $description = Request::get('description');
  $displayDate = Request::get('displayDate');
  $image = Request::get('image');
  $priority = Request::get('priority');
  $wotdId = Request::get('wotdId');

  if ($wotdId) {
    $wotd = WordOfTheDay::get_by_id($wotdId);
  } else {
    $wotd = Model::factory('WordOfTheDay')->create();
    $wotd->userId = User::getActiveId();
  }

  $today = date('Y-m-d');
  $displayDate = sanitizeDate($displayDate);
  $data = null;

  if ($displayDate === false) {
    $error = 'Data trebuie să aibă formatul AAAA-LL-ZZ';

  } else if ($displayDate &&
             !Str::startsWith($displayDate, '0000-') &&
             ($displayDate < $today)) {
    $error = 'Nu puteți atribui o dată din trecut.';

  } else if ($wotd->isPast() && ($displayDate != $wotd->displayDate)) {
    $error = 'Nu puteți modifica data pentru un cuvânt al zilei deja afișat.';

  } else if ($wotd->isPast() && ($definitionId != $wotd->definitionId)) {
    $error = 'Nu puteți modifica definiția pentru un cuvânt al zilei deja afișat.';

  } else if (!$definitionId &&
             (!$description || !$displayDate)) {
    // Use case: we notice that event X happens on date D and we want to
    // celebrate it, but we don't have the time to find a word right now.
    $error = 'Dacă nu alegeți o definiție, atunci trebuie să alegeți o dată și un motiv.';

  } else {
    $error = null;

    $wotd->displayDate = $displayDate ?: '0000-00-00';
    $wotd->definitionId = $definitionId;
    $wotd->priority = $priority;
    $wotd->image = $image;
    $wotd->description = $description;
    $wotd->modUserId = User::getActiveId();
    $wotd->save();

    $data = getWotdData($wotd->id)['data'][0];
  }

  return [
    'data' => $data,
    'error' => $error,
  ];
}

function main() {
  $action = Request::get('action');

  switch ($action) {
    case 'load': $resp = loadWotds(); break;
    case 'delete': $resp = deleteWotd(); break;
    case 'save': $resp = saveWotd(); break;
  }

  header('Content-Type: application/json');
  echo json_encode($resp);
}

main();
