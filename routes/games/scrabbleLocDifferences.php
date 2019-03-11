<?php
$list = Request::get('list');
$versions = Request::getCsv('versions');

switch ($list) {
  case 'base':
    $keyword = 'baza';
    $listType = 'forme de bază';
    break;
  case 'inflected':
    $keyword = 'flexiuni';
    $listType = 'forme flexionare';
    break;
  case 'reduced':
    $keyword = 'reduse';
    $listType = 'forme reduse';
    break;
  default:
    FlashMessage::add('Ați introdus o listă incorectă.');
    Util::redirectToRoute('games/scrabble');
}

$zipUrl = sprintf('%sdownload/scrabble/loc-dif-%s-%s-%s.zip',
                  Config::STATIC_URL, $keyword, $versions[0], $versions[1]);
$zipFile = tempnam(Config::TEMP_DIR, 'loc_') . '.zip';
$txtFile = tempnam(Config::TEMP_DIR, 'loc_') . '.txt';
if (!@copy($zipUrl, $zipFile)) {
  FlashMessage::add('Ați introdus o listă incorectă.');
  Util::redirectToRoute('games/scrabble');
}
OS::executeAndAssert("unzip -p $zipFile > $txtFile");

$diff = [];
foreach (file($txtFile) as $line) {
  $line = trim($line);
  if ($line[0] == '<') {
    $diff[] = ['del', substr($line, 2)];
  } else {
    $diff[] = ['ins', substr($line, 2)];
  }
}

@unlink($zipFile);
@unlink($txtFile);

Smart::assign([
  'keyword' => $keyword,
  'listType' => $listType,
  'versions' => $versions,
  'diff' => $diff,
  'zipUrl' => $zipUrl,
]);
Smart::display('games/scrabbleLocDifferences.tpl');
