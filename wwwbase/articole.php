<?php
require_once("../phplib/util.php");

$type = util_getRequestParameter('t');

if ($type == 'rss') {
  $articles = WikiArticle::getRss();
  $results = array();
  foreach ($articles as $a) {
    $results[] = array('title' => $a->title,
                       'description' => $a->htmlContents,
                       'pubDate' => date('D, d M Y H:i:s', $a->modDate) . ' EEST',
                       'link' => sprintf("http://%s/articol/%s", $_SERVER['HTTP_HOST'], $a->getUrlTitle()));
  }

  header("Content-type: text/xml");
  smarty_assign('rss_title', 'Articole lingvistice - DEX online');
  smarty_assign('rss_link', 'http://' . $_SERVER['HTTP_HOST'] . '/rss/articole/');
  smarty_assign('rss_description', 'Articole pe teme lingvistice de la DEX online');
  smarty_assign('rss_pubDate', date('D, d M Y H:i:s') . ' EEST');
  smarty_assign('results', $results);
  smarty_displayWithoutSkin('common/rss.ixml');
  exit;
}

smarty_assign('page_title', 'Articole lingvistice');
smarty_assign('wikiTitles', WikiArticle::loadAllTitles());
smarty_displayCommonPageWithSkin('articole.ihtml');
