<?php

const PROVERB_BIG_BANG = '2026-09-01';
const PROVERB_COLUMNS = 5;

$id = (int)Request::get('id');
$format = Request::getFormat();

$year = date('Y');

$wotm = $id ? Proverb::getProverb($id) : Proverb::getTodayProverb();
if (!$wotm) {
  Util::redirectToRoute('proverb/view'); // current proverb
}

$mysqlDate = $wotm->displayDate;
$today = date('Y-m-d', time());

if ($mysqlDate < PROVERB_BIG_BANG || (($mysqlDate > $today) && !User::can(User::PRIV_WOTD))) {
  Util::redirectToRoute('proverb/view');
}

$searchResults = array();
if ($wotm->definitionId) {
  $def = Definition::get_by_id($wotm->definitionId);
  $searchResults = SearchResult::mapDefinitionArray([$def]);
}

$crt_id = $wotm->id;
if ($crt_id > 1) {
  Smart::assign('prevmon', $crt_id - 1);
}

$nextProverb = Proverb::getProverb($crt_id + 1);
$today = date('Y-m-d');
if (($nextProverb->displayDate <= $today) || User::can(User::PRIV_ADMIN)) {
  Smart::assign('nextmon', $crt_id + 1);
} else {
  //Smart::assign('nextmon', false);
}

Smart::assign([
  'year' => $year,
  'id' => $crt_id,
  'title' => $wotm->title,
  'imageUrl' => $wotm->getLargeThumbUrl(),
  'imageXLUrl' => $wotm->getXLargeThumbUrl(),
  'imageXXLUrl' => $wotm->getXXLargeThumbUrl(),
  'artist' => WotdArtist::get_by_id($wotm->idArtist),
  'definitionId' => $wotm->definitionId,
  'reason' => $wotm->description,
  'searchResult' => array_pop($searchResults),
  //'words' => createGallery($year), //to be activated later
]);

switch ($format['name']) {
  case 'xml':
  case 'json':
    header('Content-type: '.$format['content_type']);
    Smart::displayWithoutSkin($format['tpl_path'].'/proverb.tpl');
    break;
  default:
    Smart::display('proverb/view.tpl');
}

function createGallery($year) {
  $gallery = [];
  $today = date('Y-m-d');
  $proverbs = Proverb::getProverbsFromYear($year);

  foreach ($proverbs as $expr) {
    $proverb = Proverb::GetProverb ($expr->id);
    $def = $proverb ? Definition::get_by_id($proverb->definitionId) : null;
    $visible = (($proverb->displayDate <= $today) || User::can(User::PRIV_WOTD));
    $gallery[] = [
      'wotd' => $proverb,
      'def' => $def,
      'visible' => $visible,
      'dayOfMonth' => $expr->id
    ];
  }

  // Pad end
  while (count($gallery) % PROVERB_COLUMNS != 0) {
    $gallery[] = [];
  }

  // Wrap 7 records per line
  $lines = [];
  while (count($gallery)) {
    $lines[] = array_splice($gallery, 0, PROVERB_COLUMNS);
  }
  return $lines;
}
