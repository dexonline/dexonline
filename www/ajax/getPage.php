<?php
/**
 * Translates a sourceId + word into a volume + page + image URL.
 **/
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_EDIT | User::PRIV_TRAINEE);

$sourceId = Request::get('sourceId');
$word = Request::get('word');

$source = Source::get_by_id($sourceId);

try {
  if (!$source || !$word) {
    throw new Exception('Parametri incorecți.');
  }

  $pi = PageIndex::lookup($word, $sourceId);

  if (!$pi) {
    throw new Exception('Indexul de pagini pentru această sursă este incomplet definit.');
  }

  if (!Config::PAGE_URL_PATTERN) {
    throw new Exception('Adresa paginilor scanate nu este definită în Config.php.');
  }

  header('Content-Type: application/json');
  print(json_encode([
    'volume' => $pi->volume,
    'page' => $pi->page,
  ]));

} catch (Exception $e) {
  header('HTTP/1.0 404 Not Found');
  print $e->getMessage();
}
