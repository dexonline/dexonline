<?php
require_once("../phplib/Core.php");

$type = Request::get('t');

if ($type == 'rss') {
  $articles = WikiArticle::getRss();
  $results = [];
  foreach ($articles as $a) {
    $results[] = [
      'title' => $a->title,
      'description' => $a->htmlContents,
      'pubDate' => date('D, d M Y H:i:s', $a->modDate) . ' EEST',
      'link' => sprintf("http://%s/articol/%s", $_SERVER['HTTP_HOST'], $a->getUrlTitle()),
    ];
  }

  header("Content-type: application/rss+xml");
  SmartyWrap::assign('rss_title', 'Articole lingvistice - dexonline');
  SmartyWrap::assign('rss_link', 'http://' . $_SERVER['HTTP_HOST'] . '/rss/articole/');
  SmartyWrap::assign('rss_description', 'Articole pe teme lingvistice de la dexonline');
  SmartyWrap::assign('rss_pubDate', date('D, d M Y H:i:s') . ' EEST');
  SmartyWrap::assign('results', $results);
  SmartyWrap::displayWithoutSkin('xml/rss.tpl');
  exit;
}

SmartyWrap::assign('wikiTitles', WikiArticle::loadAllTitles());
SmartyWrap::display('articole.tpl');
