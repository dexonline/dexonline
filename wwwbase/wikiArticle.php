<?php
require_once("../phplib/util.php");

$title = util_getRequestParameter('title');
$wikiTitle = WikiArticle::urlTitleToWikiTitle($title);
$wa = WikiArticle::get_by_title(addslashes($wikiTitle));

SmartyWrap::assign('wa', $wa);
SmartyWrap::assign('wikiTitles', WikiArticle::loadAllTitles());
SmartyWrap::addCss('tablesorter');
SmartyWrap::addJs('tablesorter');
SmartyWrap::display('wikiArticle.tpl');
