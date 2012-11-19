<?php
require_once("../phplib/util.php");

$title = util_getRequestParameter('title');
$wikiTitle = WikiArticle::urlTitleToWikiTitle($title);
$wa = WikiArticle::get_by_title(addslashes($wikiTitle));

if ($wa) {
  SmartyWrap::assign('wa', $wa);
  SmartyWrap::assign('page_title', $wikiTitle);
} else {
  SmartyWrap::assign('page_title', 'Articol inexistent');
}

SmartyWrap::assign('wikiTitles', WikiArticle::loadAllTitles());
SmartyWrap::displayCommonPageWithSkin('wikiArticle.ihtml');
