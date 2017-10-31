<?php
require_once('../../phplib/Core.php');
User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('sourceId');
$word = Request::get('word');
$volume = Request::get('volume');
$page = Request::get('page');

$source = Source::get_by_id($sourceId);

$word = str_replace([' ', '-'], '', $word);
$word = mb_strtolower($word);

$pi = null;

try {
  if ($source && $word) {
    $pi = Model::factory('PageIndex')
        ->where('sourceId', $sourceId)
        ->where_lte('word', $word)
        ->order_by_desc('word')
        ->find_one();

    if ($pi) {
      Util::redirect("?sourceId={$sourceId}&volume={$pi->volume}&page={$pi->page}");
    } else {
      throw new Exception('Indexul de pagini pentru această sursă este incomplet definit.');
    }
  }
  
  if ($source && $page) {
    $pi = PageIndex::get_by_sourceId_volume_page($sourceId, $volume, $page);

    if (!$pi) {
      throw new Exception('Pagina căutată nu există sau nu a fost încărcată pe server.');
    }
  }
} catch (Exception $e) {
  header('HTTP/1.0 404 Not Found');
  print $e->getMessage();
}

if ($pi) {
  $tmpFilePath = tempnam(null, 'page_');
  $f = new FtpUtil();
  $f->staticServerGet($pi->getImagePath(), $tmpFilePath);
  $image = file_get_contents($tmpFilePath);
  unlink($tmpFilePath);
  
  header('Content-Type: application/json');
  header('Cache-Control: public, max-age=31536000'); // one year
  header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000)); // onee year
  header_remove('Pragma');

  print(json_encode([
    'volume' => $pi->volume,
    'page' => $pi->page,
    'img' => base64_encode($image),
  ]));
}
