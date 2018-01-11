<?php
/**
 * Translates a sourceId + word into a volume + page + image URL.
 **/
require_once('../../phplib/Core.php');
User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('sourceId');
$word = Request::get('word');

$source = Source::get_by_id($sourceId);

$word = str_replace([' ', '-'], '', $word);
$word = mb_strtolower($word);

try {
  if (!$source || !$word) {
    throw new Exception('Parametri incorecți.');
  }

  // source-specific fixes
  if ($sourceId == 42) {
    // Șăineanu disregards diacritics when sorting, so convert the word as well.
    $word = Str::unicodeToLatin($word);
  }

  $pi = Model::factory('PageIndex')
      ->where('sourceId', $sourceId)
      ->where_lte('word', $word)
      ->order_by_desc('word')
      ->find_one();

  if (!$pi) {
    throw new Exception('Indexul de pagini pentru această sursă este incomplet definit.');
  }

  $urlPattern = Config::get('static.pageUrlPattern');
  if (!$urlPattern) {
    throw new Exception('Adresa paginilor scanate nu este definită în dex.conf.');
  }

  header('Content-Type: application/json');
  print(json_encode([
    'urlPattern' => $urlPattern,
    'volume' => $pi->volume,
    'page' => $pi->page,
  ]));

} catch (Exception $e) {
  header('HTTP/1.0 404 Not Found');
  print $e->getMessage();
}
