<?php
require_once('../phplib/Core.php');
User::mustHave(User::PRIV_EDIT);

$sourceId = Request::get('sourceId');
$word = Request::get('word');
$volume = Request::get('volume');
$page = Request::get('page');

$source = Source::get_by_id($sourceId);

$word = str_replace([' ', '-'], '', $word);
$word = mb_strtolower($word);

$pi = null;

if ($source && $word) {
  $pi = Model::factory('PageIndex')
      ->where('sourceId', $sourceId)
      ->where_lte('word', $word)
      ->order_by_desc('word')
      ->find_one();

  if (!$pi) {
    FlashMessage::add('Indexul de pagini pentru această sursă este incomplet definit.');
  }
} else if ($source && $page) {
  $pi = PageIndex::get_by_sourceId_volume_page($sourceId, $volume, $page);

  if (!$pi) {
    SmartyWrap::assign('volume', $volume);
    SmartyWrap::assign('page', $page);
    FlashMessage::add('Pagina căutată nu există sau nu a fost încărcată pe server.');
  }
}

if ($pi) {
  $tmpFilePath = tempnam(null, 'page_');
  $f = new FtpUtil();
  $f->staticServerGet($pi->getPdfPath(), $tmpFilePath);
  $pdf = file_get_contents($tmpFilePath);
  unlink($tmpFilePath);

  SmartyWrap::assign('volume', $pi->volume);
  SmartyWrap::assign('page', $pi->page);
  SmartyWrap::assign('pdfBase64', base64_encode($pdf));
}

SmartyWrap::assign('sourceId', $sourceId);
SmartyWrap::assign('word', $word);
SmartyWrap::display('showPage.tpl');
