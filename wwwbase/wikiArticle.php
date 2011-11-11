<?php
require_once("../phplib/util.php");

$title = util_getRequestParameter('title');
$wikiTitle = WikiArticle::urlTitleToWikiTitle($title);
$wa = WikiArticle::get_by_title(addslashes($wikiTitle));

if ($wa) {
  smarty_assign('wa', $wa);
  smarty_assign('page_title', $wikiTitle);
} else {
  smarty_assign('page_title', 'Articol inexistent');
}

smarty_assign('wikiTitles', WikiArticle::loadAllTitles());
smarty_displayCommonPageWithSkin('wikiArticle.ihtml');
