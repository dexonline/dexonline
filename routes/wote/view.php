<?php

const WOTE_BIG_BANG = '2022-01-01';
const WOTE_COLUMNS = 5;

$id = (int)Request::get('id');
$format = Request::getFormat();

$year = date('Y');

$wotm = $id ? ExpressionOfTheMonth::getExpression($id) :  ExpressionOfTheMonth::getTodayExpression();
if (!$wotm) {
  Util::redirectToRoute('wote/view'); // current expression
}
$def = Definition::get_by_id($wotm->definitionId);
$crt_id = $wotm->id;

$searchResults = SearchResult::mapDefinitionArray([$def]);
if ($crt_id > 1) {
  Smart::assign('prevmon', $crt_id - 1);
}

$nextWote = ExpressionOfTheMonth::getExpression($crt_id + 1);
$today = date('Y-m-d');
if ($nextWote->displayDate < $today || User::can(User::PRIV_ADMIN)) {
  Smart::assign('nextmon', $crt_id + 1);
} else {
  Smart::assign('nextmon', false);
}

Smart::assign([
  'year' => $year,
  'id' => $crt_id,
  'title' => $wotm->title,
  'imageUrl' => $wotm->getLargeThumbUrl(),
  'imageXLUrl' => $wotm->getXLargeThumbUrl(),
  'imageXXLUrl' => $wotm->getXXLargeThumbUrl(),
  'artist' => WotdArtist::get_by_id($wotm->idArtist),
  'reason' => $wotm->description,
  'searchResult' => array_pop($searchResults),
  'words' => createGallery($year),
]);

switch ($format['name']) {
  case 'xml':
  case 'json':
    header('Content-type: '.$format['content_type']);
    Smart::displayWithoutSkin($format['tpl_path'].'/wote.tpl');
    break;
  default:
    Smart::display('wote/view.tpl');
}

function createGallery($year) {
  $gallery = [];
  $today = date('Y-m-d');
  $expressions = ExpressionOfTheMonth::getExpressionsFromYear($year);

  foreach ($expressions as $expr) {
    $wote = ExpressionOfTheMonth::GetExpression ($expr->id);
    $def = $wote ? Definition::get_by_id($wote->definitionId) : null;
    $visible = (($wote->displayDate <= $today) || User::can(User::PRIV_WOTD));
    $gallery[] = [
      'wotd' => $wote,
      'def' => $def,
      'visible' => $visible,
      'dayOfMonth' => $expr->id
    ];
  }

  // Pad end
  while (count($gallery) % WOTE_COLUMNS != 0) {
    $gallery[] = [];
  }

  // Wrap 7 records per line
  $lines = [];
  while (count($gallery)) {
    $lines[] = array_splice($gallery, 0, WOTE_COLUMNS);
  }
  return $lines;
}
