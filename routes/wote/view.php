<?php

const WOTE_BIG_BANG = '2022-01-01';

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
Smart::assign('nextmon', $crt_id + 1);

Smart::assign([
  'year' => $year,
  'id' => $crt_id,
  'title' => $wotm->title,
  'imageUrl' => $wotm->getLargeThumbUrl(),
  'artist' => $wotm->getArtist(),
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

  $expressions = ExpressionOfTheMonth::getExpressionsFromYear($year);
  foreach ($expressions as $expr) {
    $wote = ExpressionOfTheMonth::GetExpression ($expr->id);
    $def = $wote ? Definition::get_by_id($wote->definitionId) : null;
    $gallery[] = [
      'wotd' => $wote,
      'def' => $def,
      'visible' => 1,
      'dayOfMonth' => $expr->id
    ];
  }

  // Pad end
  while (count($gallery) % 7 != 0) {
    $gallery[] = [];
  }

  // Wrap 7 records per line
  $lines = [];
  while (count($gallery)) {
    $lines[] = array_splice($gallery, 0, 7);
  }
  return $lines;
}
